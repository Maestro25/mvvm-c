<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\RBAC\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910220500_create_permission_table_only
 *
 * Creates the 'permission' main table,
 * including all audit metadata fields for lifecycle info.
 * Audit event details are centralized in the global audit_log table.
 */
class Migration_20250910220500_create_permission_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910220500_create_permission_table';
    }

    public function getDescription(): string
    {
        return 'Creates main permission table with audit metadata fields; audit handled by global audit_log table.';
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS permission (
                id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID v4',
                name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Permission name e.g., product_create',
                description VARCHAR(255) DEFAULT NULL COMMENT 'Permission description',
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
            COMMENT='Main permissions table with audit metadata';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS permission;");
    }
}
