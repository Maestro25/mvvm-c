<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations;

class MigrationCLI
{
    private MigrationManager $manager;
    private array $argv;
    private array $options = [];

    public function __construct(MigrationManager $manager, array $argv)
    {
        $this->manager = $manager;
        $this->argv = $argv;
        $this->parseOptions();
    }

    private function parseOptions(): void
    {
        $args = $this->argv;
        array_shift($args); // remove script name

        if (empty($args)) {
            $this->usageAndExit("No command provided.");
        }

        $this->options['command'] = $args[0];
        $args = array_slice($args, 1);

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $eqPos = strpos($arg, '=');
                if ($eqPos !== false) {
                    $key = substr($arg, 2, $eqPos - 2);
                    $value = substr($arg, $eqPos + 1);
                } else {
                    $key = substr($arg, 2);
                    $value = true;
                }
                $this->options[$key] = $value;
            }
        }
    }

    public function run(): void
    {
        $command = $this->options['command'] ?? null;
        $verbose = !($this->options['quiet'] ?? false) && ($this->options['verbose'] ?? false);
        $dryRun = isset($this->options['dry-run']);
        $steps = isset($this->options['steps']) ? (int)$this->options['steps'] : null;
        $version = $this->options['version'] ?? null;

        try {
            switch ($command) {
                case 'migrate:up':
                    $this->manager->migrateUp($steps, $version, $dryRun, $verbose);
                    break;

                case 'migrate:down':
                    // Use rollbackMultiple supporting steps and verbose
                    $this->manager->rollbackMultiple($steps, $verbose);
                    break;

                case 'migrate:status':
                    $this->displayStatus();
                    break;

                default:
                    $this->usageAndExit("Unknown command: $command");
            }
        } catch (\Throwable $ex) {
            fwrite(STDERR, "Error: " . $ex->getMessage() . PHP_EOL);
            exit(1);
        }
    }

    private function displayStatus(): void
    {
        $pending = $this->manager->getPendingMigrations();
        echo "Pending migrations:\n";
        if (empty($pending)) {
            echo " - None\n";
        } else {
            foreach ($pending as $v) {
                echo " - {$v}\n";
            }
        }
    }

    private function usageAndExit(?string $message = null): void
    {
        if ($message !== null) {
            fwrite(STDERR, $message . PHP_EOL);
        }
        $usage = <<<USAGE
Usage: php migrate.php <command> [options]

Commands:
  migrate:up           Apply pending migrations
  migrate:down         Rollback migrations
  migrate:status       Show pending migrations

Options:
  --steps=N            Number of migrations to apply/rollback
  --version=VERSION    Target migration version (for migrate:up)
  --dry-run            Show but don't apply
  --verbose            Show detailed output
  --quiet              Reduce output

Examples:
  php migrate.php migrate:up --verbose
  php migrate.php migrate:down --steps=2 --verbose
  php migrate.php migrate:status

USAGE;
        fwrite(STDERR, $usage);
        exit($message === null ? 0 : 1);
    }
}
