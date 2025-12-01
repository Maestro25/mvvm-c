<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations\Dev;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;
use RuntimeException;

/**
 * Abstract class AbstractMigrationBase
 * Provides helper methods and conventions for schema migrations.
 * Enforces idempotency and FK-aware schema modifications.
 */
abstract class AbstractMigrationBase implements MigrationInterface
{
    /**
     * Migration version (Semantic versioning recommended).
     */
    abstract public function getVersion(): string;

    /**
     * Short human-readable description.
     */
    abstract public function getDescription(): string;

    /**
     * Explicit dependencies versions, default empty.
     *
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [];
    }

    /**
     * Apply migration: Implement database schema changes.
     * Use provided helper methods for FK-aware create/alter/drop.
     *
     * @param SafeMySQL $db
     */
    abstract public function up(SafeMySQL $db): void;

    /**
     * Rollback migration, revert all changes from up().
     *
     * @param SafeMySQL $db
     */
    abstract public function down(SafeMySQL $db): void;

    /**
     * Checks if a table exists.
     */
    protected function tableExists(SafeMySQL $db, string $tableName): bool
    {
        $row = $db->getRow("SHOW TABLES LIKE ?s", $tableName);
        return $row !== null;
    }

    /**
     * Checks if a column exists in a table.
     */
    protected function columnExists(SafeMySQL $db, string $tableName, string $columnName): bool
    {
        $row = $db->getRow("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?s AND COLUMN_NAME = ?s
        ", $tableName, $columnName);
        return $row !== null;
    }

    /**
     * Create table if not exists, FK-aware, idempotent.
     * 
     * Example usage requires raw SQL but should be minimal and safe.
     *
     * @param SafeMySQL $db
     * @param string $tableName
     * @param string $schema SQL fragment defining columns, PK, and FK constraints
     */
    protected function createTableIfNotExists(SafeMySQL $db, string $tableName, string $schema): void
    {
        if ($this->tableExists($db, $tableName)) {
            return;
        }
        $sql = sprintf("CREATE TABLE `%s` (%s) ENGINE=InnoDB", $tableName, $schema);
        $db->query($sql);
    }

    /**
     * Drop table if exists.
     *
     * @param SafeMySQL $db
     * @param string $tableName
     */
    protected function dropTableIfExists(SafeMySQL $db, string $tableName): void
    {
        if ($this->tableExists($db, $tableName)) {
            $db->query("DROP TABLE `$tableName`");
        }
    }

    /**
     * Add column if not exists (idempotency).
     *
     * @param SafeMySQL $db
     * @param string $tableName
     * @param string $columnDef Full column definition SQL fragment e.g. "new_col INT NOT NULL"
     */
    protected function addColumnIfNotExists(SafeMySQL $db, string $tableName, string $columnDef): void
    {
        // Extract column name (first token before space)
        $parts = preg_split('/\s+/', trim($columnDef), 2);
        $colName = $parts[0];
        if ($this->columnExists($db, $tableName, $colName)) {
            return;
        }
        $db->query("ALTER TABLE `$tableName` ADD COLUMN $columnDef");
    }

    /**
     * Drop column if exists.
     *
     * @param SafeMySQL $db
     * @param string $tableName
     * @param string $columnName
     */
    protected function dropColumnIfExists(SafeMySQL $db, string $tableName, string $columnName): void
    {
        if ($this->columnExists($db, $tableName, $columnName)) {
            $db->query("ALTER TABLE `$tableName` DROP COLUMN `$columnName`");
        }
    }

    /**
     * Add foreign key constraint if not exists.
     * Naming convention for constraints: fk_{table}_{column}
     *
     * @param SafeMySQL $db
     * @param string $tableName
     * @param string $columnName
     * @param string $refTable
     * @param string $refColumn
     * @param string $onDelete Default 'RESTRICT'
     * @param string $onUpdate Default 'CASCADE'
     */
    protected function addForeignKeyIfNotExists(
        SafeMySQL $db,
        string $tableName,
        string $columnName,
        string $refTable,
        string $refColumn,
        string $onDelete = 'RESTRICT',
        string $onUpdate = 'CASCADE'
    ): void {
        $fkName = "fk_{$tableName}_{$columnName}";
        if ($this->foreignKeyExists($db, $tableName, $fkName)) {
            return;
        }
        $sql = sprintf(
            "ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON DELETE %s ON UPDATE %s",
            $tableName,
            $fkName,
            $columnName,
            $refTable,
            $refColumn,
            $onDelete,
            $onUpdate
        );
        $db->query($sql);
    }

    /**
     * Drop foreign key if exists.
     *
     * @param SafeMySQL $db
     * @param string $tableName
     * @param string $fkName
     */
    protected function dropForeignKeyIfExists(SafeMySQL $db, string $tableName, string $fkName): void
    {
        if ($this->foreignKeyExists($db, $tableName, $fkName)) {
            $db->query("ALTER TABLE `$tableName` DROP FOREIGN KEY `$fkName`");
        }
    }

    /**
     * Check if foreign key exists on a table.
     */
    protected function foreignKeyExists(SafeMySQL $db, string $tableName, string $fkName): bool
    {
        $row = $db->getRow("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?s AND CONSTRAINT_NAME = ?s
        ", $tableName, $fkName);
        return $row !== null;
    }

    /**
     * Utility to sanitize table/column names to prevent injection.
     * Only allow alphanumeric, underscore and dash.
     */
    protected function sanitizeName(string $name): string
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new RuntimeException("Invalid identifier: $name");
        }
        return $name;
    }
}
