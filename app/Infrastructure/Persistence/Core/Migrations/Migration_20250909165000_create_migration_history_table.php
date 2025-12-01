<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration 20250909165000_create_migration_history_table
 *
 * Creates the migration_history table to track applied migrations.
 */
class Migration_20250909165000_create_migration_history_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250909165000_create_migration_history_table';
    }

    public function getDescription(): string
    {
        return 'Create migration_history table';
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS migration_history (
                version VARCHAR(64) PRIMARY KEY,
                applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                hash CHAR(64) NOT NULL
            ) ENGINE=InnoDB;
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS migration_history;");
    }
}
