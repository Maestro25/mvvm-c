<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910221500_create_user_table
 *
 * Creates the 'user' table with essential audit metadata fields.
 * Audit events are recorded in global audit_log table.
 */
class Migration_20250910221500_create_user_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910221500_create_user_table';
    }

    public function getDescription(): string
    {
        return 'Creates user table with audit metadata fields; detailed audit in global audit_log.';
    }

    public function getDependencies(): array
    {
        return [
            '20250910220700_create_roles_permission_table',
            '20250910221000_create_user_status_table'
        ];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS user (
                id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID PRIMARY KEY',
                username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique username',
                email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Unique email for login/recovery',
                password_hash VARCHAR(255) NOT NULL COMMENT 'Hashed password only, no plaintext',
                status CHAR(36) NOT NULL COMMENT 'FK user_status(id) UUID',

                -- Audit metadata fields for lifecycle tracking
                created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'Record creation timestamp',
                created_by BINARY(16) DEFAULT NULL COMMENT 'UUID of user who created this record',
                created_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of creator (IPv4/IPv6)',

                updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT 'Last update timestamp',
                updated_by BINARY(16) DEFAULT NULL COMMENT 'UUID of user who last updated this record',
                updated_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of last updater (IPv4/IPv6)',

                deleted_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
                deleted_by BINARY(16) DEFAULT NULL COMMENT 'UUID of user who soft deleted the record',
                deleted_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of deleter (IPv4/IPv6)',

                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',
                CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),

                CONSTRAINT fk_user_status FOREIGN KEY (status) REFERENCES user_status(id) ON DELETE RESTRICT ON UPDATE CASCADE,
                INDEX idx_user_status (status),
                INDEX idx_user_username (username),
                INDEX idx_user_email (email)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS user;");
    }
}
