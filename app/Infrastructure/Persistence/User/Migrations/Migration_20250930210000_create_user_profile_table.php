<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250930210000_create_user_profile_table
 *
 * Creates 'user_profile' table with core profile data and soft delete.
 * Audit trail handled via centralized global audit_log table.
 */
class Migration_20250930210000_create_user_profile_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250930210000_create_user_profile_table';
    }

    public function getDescription(): string
    {
        return 'Creates user_profile table separating core profile data from audit trail.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS user_profile (
                user_id CHAR(36) NOT NULL PRIMARY KEY,
                first_name VARCHAR(100) DEFAULT NULL,
                last_name VARCHAR(100) DEFAULT NULL,
                phone VARCHAR(25) DEFAULT NULL,
                address_line1 VARCHAR(255) DEFAULT NULL,
                address_line2 VARCHAR(255) DEFAULT NULL,
                city VARCHAR(100) DEFAULT NULL,
                state VARCHAR(100) DEFAULT NULL,
                postal_code VARCHAR(20) DEFAULT NULL,
                country VARCHAR(100) DEFAULT NULL,
                profile_picture VARCHAR(255) DEFAULT NULL COMMENT 'Path or URL to profile image',
                preferences JSON DEFAULT NULL COMMENT 'Serialized user preferences',
                gender VARCHAR(10) DEFAULT NULL COMMENT 'User gender',

                created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
                created_by BINARY(16) DEFAULT NULL COMMENT 'User UUID who created',
                created_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of creator',

                updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
                updated_by BINARY(16) DEFAULT NULL COMMENT 'User UUID who last updated',
                updated_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of last updater',

                deleted_at TIMESTAMP(6) NULL DEFAULT NULL,
                deleted_by BINARY(16) DEFAULT NULL COMMENT 'User UUID who soft deleted',
                deleted_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of deleter',

                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',

                CONSTRAINT fk_user_profile_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
                INDEX idx_user_profile_deleted_at (deleted_at)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='User profile details with soft delete and audit metadata';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS user_profile;");
    }
}
