<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\RBAC\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910220700_create_roles_permission_table
 *
 * Creates many-to-many junction table roles_permission linking roles and permissions.
 * Uses foreign keys with cascade on update/delete for referential integrity.
 */
class Migration_20250910220700_create_roles_permission_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910220700_create_roles_permission_table';
    }

    public function getDescription(): string
    {
        return 'Creates roles_permission junction table for RBAC linking roles and permissions.';
    }

    public function getDependencies(): array
    {
        return [
            '20250910220500_create_permission_table',
            '20250910220600_create_roles_table'
        ];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS roles_permission (
                role_id CHAR(36) NOT NULL,
                permission_id CHAR(36) NOT NULL,
                
                PRIMARY KEY (role_id, permission_id),

                CONSTRAINT fk_roles_permission_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_roles_permission_permission FOREIGN KEY (permission_id) REFERENCES permission(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Junction table linking roles and permissions for RBAC';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS roles_permission;");
    }
}
