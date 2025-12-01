<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251017093000_create_user_remember_me_table
 *
 * Creates 'user_remember_me' table for persistent login tokens with core data.
 * Audit logging is handled via the centralized global audit_log table through triggers.
 */
class Migration_20251017093000_create_user_remember_me_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251017093000_create_user_remember_me_table';
    }

    public function getDescription(): string
    {
        return 'Creates user_remember_me table for persistent login tokens; audit via global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS user_remember_me (
                user_id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'FK to user(id)',
                remember_me_token CHAR(64) NOT NULL COMMENT 'Remember me login token',
                remember_me_token_expires_at TIMESTAMP(6) NOT NULL COMMENT 'Token expiration timestamp',
                used_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'When token was used',
                revoked BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Has token been revoked',
                revoked_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Revocation timestamp',
                revoked_by CHAR(36) NULL COMMENT 'User/admin who revoked token',
                reason_for_revocation TEXT NULL COMMENT 'Reason for token revocation',
                token_hash_version SMALLINT NOT NULL DEFAULT 1 COMMENT 'Hashing algorithm version',
                device_info VARCHAR(255) NULL COMMENT 'Device or user agent info',
                origin VARCHAR(64) NULL COMMENT 'Token issuance context or client app',
                created_by CHAR(36) NOT NULL COMMENT 'Creator user id',
                created_ip VARBINARY(16) NOT NULL COMMENT 'Creator IP address',
                updated_by CHAR(36) NULL COMMENT 'Updater user id',
                updated_ip VARBINARY(16) NULL COMMENT 'Updater IP address',

                CONSTRAINT fk_remember_me_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS user_remember_me;");
    }
}
