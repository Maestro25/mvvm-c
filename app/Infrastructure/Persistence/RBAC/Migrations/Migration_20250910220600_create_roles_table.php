<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\RBAC\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910220600_create_roles_table_only
 *
 * Creates the 'roles' main table,
 * now including audit metadata fields for created/updated/deleted lifecycle info.
 * Audit event details are still recorded in the global audit_log table.
 */
class Migration_20250910220600_create_roles_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910220600_create_roles_table';
    }

    public function getDescription(): string
    {
        return 'Creates main roles table with audit metadata fields; audit handled by global audit_log table.';
    }

    public function getDependencies(): array
    {
        return ['20250910220500_create_permission_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS roles (
                id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID v4',
                name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Role name e.g., admin',
                description VARCHAR(255) DEFAULT NULL COMMENT 'Role description',
                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',

                created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'Record creation timestamp',
                created_by BINARY(16) NOT NULL COMMENT 'UUID of creator',
                created_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of creator (IPv4/IPv6)',

                updated_at TIMESTAMP(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6) COMMENT 'Record last update timestamp',
                updated_by BINARY(16) DEFAULT NULL COMMENT 'UUID of last updater',
                updated_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of last updater (IPv4/IPv6)',

                deleted_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
                deleted_by BINARY(16) DEFAULT NULL COMMENT 'UUID of deleter',
                deleted_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of deleter (IPv4/IPv6)'
            ) ENGINE=InnoDB
              CHARSET=utf8mb4 
              COLLATE=utf8mb4_unicode_ci
            COMMENT='Main roles table with audit metadata';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS roles;");
    }
}
