<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910221000_create_user_status_table
 *
 * Creates user_status lookup table for user status enumeration.
 * Lookup table with UUID primary key and unique status names.
 */
class Migration_20250910221000_create_user_status_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910221000_create_user_status_table';
    }

    public function getDescription(): string
    {
        return 'Creates user_status enumeration lookup table.';
    }

    public function getDependencies(): array
    {
        return ['20250910220700_create_roles_permission_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS user_status (
                id CHAR(36) NOT NULL PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique name for user status',
                description VARCHAR(255) DEFAULT NULL COMMENT 'Optional description for user status'
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
            COMMENT='User status enumeration lookup table';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS user_status;");
    }
}
