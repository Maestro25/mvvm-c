<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations;

use SafeMySQL;
use RuntimeException;
use LogicException;
use Throwable;

class MigrationManager
{
    private SafeMySQL $db;
    /**
     * @var array<string, MigrationInterface> Version => Migration instance
     */
    private array $migrations = [];

    private string $lockName = 'migration_lock';
    private int $lockTimeoutSec = 300;

    public function __construct(SafeMySQL $db)
    {
        $this->db = $db;
        $this->ensureMigrationHistoryTableExists();
    }

    public function registerMigration(string $version, MigrationInterface $migration): void
    {
        $this->validateVersionFormat($version);

        if (isset($this->migrations[$version])) {
            throw new LogicException("Duplicate migration version registered: $version");
        }

        $this->migrations[$version] = $migration;
    }

    public function registerMigrations(array $migrations): void
    {
        foreach ($migrations as $version => $migration) {
            $this->registerMigration($version, $migration);
        }
    }

    private function validateVersionFormat(string $version): void
    {
        if (!preg_match('/^\d{14}(_[a-z0-9_]+)?$/i', $version)) {
            throw new \InvalidArgumentException("Invalid migration version format: $version. Expected YYYYMMDDHHMMSS[_suffix].");
        }
    }

    private function resolveAndSort(array $versions): array
    {
        $sorted = $versions;
        usort($sorted, fn ($a, $b) => strcmp($a, $b));
        return $sorted;
    }

    public function getPendingMigrations(): array
    {
        $applied = $this->db->getCol("SELECT version FROM migration_history");
        $pending = array_filter(array_keys($this->migrations), fn ($v) => !in_array($v, $applied, true));
        return $this->resolveAndSort($pending);
    }

    private function ensureMigrationHistoryTableExists(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS migration_history (
                version VARCHAR(64) PRIMARY KEY,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                hash CHAR(64) NOT NULL
            ) ENGINE=InnoDB"
        );
    }

    private function output(string $message, bool $verbose): void
    {
        if ($verbose) {
            echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        }
    }

    public function migrateUp(?int $steps = null, ?string $targetVersion = null, bool $dryRun = false, bool $verbose = false): void
    {
        $this->acquireLock();

        try {
            $this->preMigration();
            $appliedCount = 0;
            $previousPendingCount = null;

            while (true) {
                $pending = $this->getPendingMigrations();

                if ($targetVersion !== null) {
                    $this->validateVersionFormat($targetVersion);
                    $pending = array_filter($pending, fn ($v) => strcmp($v, $targetVersion) <= 0);
                }

                if ($steps !== null) {
                    $pending = array_slice($pending, 0, $steps - $appliedCount, true);
                }

                $currentPendingCount = count($pending);
                if ($previousPendingCount !== null && $currentPendingCount === $previousPendingCount) {
                    $this->output("No progress made in reducing pending migrations. Ending to prevent infinite loop.", $verbose);
                    break;
                }
                $previousPendingCount = $currentPendingCount;

                if (empty($pending)) {
                    if ($appliedCount === 0) {
                        $this->output("No migrations to apply.", $verbose);
                    }
                    break;
                }

                if ($dryRun) {
                    $this->output("Dry-run - migrations to apply: " . implode(', ', $pending), $verbose);
                    break;
                }

                $toApply = $this->resolveAndSort($pending);
                $this->applyMigrations($toApply, $verbose);
                $appliedCount += count($toApply);

                if ($steps !== null && $appliedCount >= $steps) {
                    break;
                }
            }

            $this->postMigration();
        } catch (Throwable $ex) {
            $this->releaseLock();
            throw $ex;
        }

        $this->releaseLock();
    }

    /**
     * Applies a list of migrations in order
     *
     * @param array<string> $toApply
     * @param bool $verbose
     */
    private function applyMigrations(array $toApply, bool $verbose): void
    {
        $this->output("Starting migration loop for " . count($toApply) . " migrations.", $verbose);
        foreach ($toApply as $version) {
            $migration = $this->migrations[$version] ?? null;
            if ($migration === null) {
                throw new LogicException("No migration found with version $version");
            }

            $this->output("Applying migration $version - {$migration->getDescription()}", $verbose);

            try {
                $this->db->query("START TRANSACTION");

                $exists = $this->db->getOne("SELECT COUNT(1) FROM migration_history WHERE version = ?s", $version);
                if ($exists) {
                    $this->output("Skipping migration $version - already applied.", $verbose);
                    $this->db->query("COMMIT");
                    continue;
                }

                $this->output("Running up() method for migration $version", $verbose);
                $migration->up($this->db);
                $this->output("Completed up() method for migration $version", $verbose);

                $hash = hash('sha256', serialize($migration));
                $insertResult = $this->db->query("INSERT INTO migration_history (version, hash) VALUES (?s, ?s)", $version, $hash);
                if (!$insertResult) {
                    throw new RuntimeException("Failed to insert migration record for $version");
                }

                $this->db->query("COMMIT");
                $this->output("Transaction committed for migration $version", $verbose);
                $this->output("Successfully applied migration $version.", $verbose);
            } catch (Throwable $ex) {
                $this->db->query("ROLLBACK");
                $this->output("Failed to apply migration $version: " . $ex->getMessage(), $verbose);
                throw $ex;
            }
        }
        $this->output("Completed applying all migrations.", $verbose);
    }

    /**
     * Rolls back the last $steps migrations.
     *
     * @param int $steps Number of migrations to rollback
     * @param bool $verbose Whether to output verbose logs
     */
    public function rollback(int $steps = 1, bool $verbose = false): void
    {
        $this->acquireLock();

        try {
            $this->preMigration();

            $applied = $this->getAppliedMigrations();

            if (empty($applied)) {
                $this->output("No migrations to rollback.", $verbose);
                return;
            }

            $toRollback = array_slice(array_reverse($applied), 0, $steps);

            foreach ($toRollback as $version) {
                $migration = $this->migrations[$version] ?? null;
                if ($migration === null) {
                    $this->output("Migration $version not found for rollback.", $verbose);
                    continue;
                }

                $this->output("Rolling back migration $version - {$migration->getDescription()}", $verbose);

                try {
                    $this->db->query("START TRANSACTION");

                    $migration->down($this->db);

                    $deleteCount = $this->db->query("DELETE FROM migration_history WHERE version = ?s", $version);
                    if ($deleteCount === 0) {
                        $this->output("Warning: Migration record $version missing on rollback.", $verbose);
                    }

                    $this->db->query("COMMIT");
                    $this->output("Rollback committed for migration $version", $verbose);
                } catch (Throwable $ex) {
                    $this->db->query("ROLLBACK");
                    $this->output("Failed to rollback migration $version: " . $ex->getMessage(), $verbose);
                    throw $ex;
                }
            }

            $this->postMigration();
        } catch (Throwable $ex) {
            $this->releaseLock();
            throw $ex;
        }

        $this->releaseLock();
    }

    /**
     * Returns ordered list of applied migrations
     *
     * @return array<string>
     */
    public function getAppliedMigrations(): array
    {
        $applied = $this->db->getCol("SELECT version FROM migration_history");
        return $this->resolveAndSort($applied);
    }

    /**
     * Rolls back multiple migrations by steps
     *
     * @param int|null $steps Number of migrations to rollback, null means rollback all
     * @param bool $verbose Verbose output flag
     */
    public function rollbackMultiple(?int $steps = null, bool $verbose = false): void
    {
        $this->acquireLock();

        try {
            $this->preMigration();

            $applied = $this->getAppliedMigrations();

            if (empty($applied)) {
                $this->output("No migrations to rollback.", $verbose);
                return;
            }

            $total = count($applied);

            $toRollbackCount = $steps === null ? $total : min($steps, $total);

            $toRollback = array_slice(array_reverse($applied), 0, $toRollbackCount);

            foreach ($toRollback as $version) {
                $migration = $this->migrations[$version] ?? null;
                if ($migration === null) {
                    $this->output("Migration $version not found for rollback.", $verbose);
                    continue;
                }

                $this->output("Rolling back migration $version - {$migration->getDescription()}", $verbose);

                try {
                    $this->db->query("START TRANSACTION");

                    $migration->down($this->db);

                    $deleteCount = $this->db->query("DELETE FROM migration_history WHERE version = ?s", $version);
                    if ($deleteCount === 0) {
                        $this->output("Warning: Migration record $version missing on rollback.", $verbose);
                    }

                    $this->db->query("COMMIT");
                    $this->output("Rollback committed for migration $version", $verbose);
                } catch (Throwable $ex) {
                    $this->db->query("ROLLBACK");
                    $this->output("Failed to rollback migration $version: " . $ex->getMessage(), $verbose);
                    throw $ex;
                }
            }

            $this->postMigration();
        } catch (Throwable $ex) {
            $this->releaseLock();
            throw $ex;
        }

        $this->releaseLock();
    }

    private function acquireLock(): void
    {
        // Implement database or file system locking as required for concurrency safety
    }

    private function releaseLock(): void
    {
        // Implement lock release
    }

    private function preMigration(): void
    {
        // Hook for preparations e.g. logging, backup
    }

    private function postMigration(): void
    {
        // Hook for cleanup e.g. logging
    }
}
