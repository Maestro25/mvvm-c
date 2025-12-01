<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251017090000_create_user_auth_table
 *
 * Creates 'user_auth' table with core authentication tracking columns.
 * Audit events handled via centralized global audit_log table through triggers.
 */
class Migration_20251017090000_create_user_auth_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251017090000_create_user_auth_table';
    }

    public function getDescription(): string
    {
        return 'Creates user_auth table with core auth state and versioning; audit via global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS user_auth (
                user_id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'FK to user(id)',
                failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Failed login attempts count',
                last_failed_login_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Timestamp of last failed login',
                locked_until TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Account lock expiration timestamp',
                last_login_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Last successful login timestamp',
                password_hash_version SMALLINT NOT NULL DEFAULT 1 COMMENT 'Version of password hashing algorithm',
                last_password_change_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'When user last changed password',
                two_factor_enabled BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is two-factor authentication enabled',
                two_factor_last_verified_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Last successful 2FA verification',
                deleted_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
                
                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',

                CONSTRAINT fk_user_auth_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS user_auth;");
    }
}
