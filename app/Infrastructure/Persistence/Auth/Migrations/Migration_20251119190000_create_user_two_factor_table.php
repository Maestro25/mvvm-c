<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251119190000_create_user_two_factor_table
 *
 * Creates a dedicated table for handling user two-factor authentication (2FA) methods.
 * Supports multiple 2FA methods per user, with encrypted secrets and audit metadata.
 */
class Migration_20251119190000_create_user_two_factor_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251119190000_create_user_two_factor_table';
    }

    public function getDescription(): string
    {
        return 'Creates user_two_factor table for 2FA methods with audit metadata.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS user_two_factor (
                id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID primary key',
                user_id CHAR(36) NOT NULL COMMENT 'FK to user(id)',
                type ENUM('totp', 'sms', 'u2f', 'backup_code') NOT NULL COMMENT '2FA method type',
                secret VARBINARY(255) NOT NULL COMMENT 'Encrypted secret or credential data',
                is_primary BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Is primary 2FA method for user',
                enabled BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Is this 2FA method currently enabled',
                last_verified_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Last successful verification timestamp',
                created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
                updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
                deleted_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',

                CONSTRAINT fk_user_two_factor_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                INDEX idx_user_two_factor_user_id (user_id),
                INDEX idx_user_two_factor_type (type),
                INDEX idx_user_two_factor_enabled (enabled),
                INDEX idx_user_two_factor_is_primary (is_primary)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS user_two_factor;");
    }
}
