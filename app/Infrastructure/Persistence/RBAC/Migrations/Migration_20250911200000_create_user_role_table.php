<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\RBAC\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250911200000_create_user_role_table
 *
 * Creates user_role many-to-many junction table for user-role assignments.
 */
class Migration_20250911200000_create_user_role_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250911200000_create_user_role_table';
    }

    public function getDescription(): string
    {
        return 'Creates user_role junction table for many-to-many user-role mapping with foreign keys and indexes';
    }

    public function getDependencies(): array
    {
        // Depend on user and role tables existing
        return [
            '20250910221500_create_user_and_user_audit_tables',
            '20250910221510_create_role_table'
        ];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS user_role (
                user_id CHAR(36) NOT NULL,
                role_id CHAR(36) NOT NULL,

                PRIMARY KEY (user_id, role_id),

                CONSTRAINT fk_user_role_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_user_role_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,

                INDEX idx_user_role_user_id (user_id),
                INDEX idx_user_role_role_id (role_id)
            ) ENGINE=InnoDB
              DEFAULT CHARSET=utf8mb4
              COLLATE=utf8mb4_unicode_ci
              COMMENT='Junction table linking users and roles for RBAC';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS user_role;");
    }
}
