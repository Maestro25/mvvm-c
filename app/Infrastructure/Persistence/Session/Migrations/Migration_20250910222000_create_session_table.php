<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Session\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910222000_create_session_table
 *
 * Creates 'session' table with core session data fields,
 * including audit metadata fields for lifecycle tracking.
 * Audit events still recorded in centralized global audit_log table.
 */
class Migration_20250910222000_create_session_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910222000_create_session_table';
    }

    public function getDescription(): string
    {
        return 'Creates session table with audit metadata fields; audit via global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS session (
                id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID PRIMARY KEY',
                user_id CHAR(36) NOT NULL COMMENT 'FK to user(id)',
                session_token CHAR(64) NOT NULL UNIQUE,
                session_token_expires_at TIMESTAMP(6) NULL DEFAULT NULL,
                refresh_token CHAR(128) DEFAULT NULL,
                refresh_token_expires_at TIMESTAMP(6) NULL DEFAULT NULL,
                csrf_token VARCHAR(255) DEFAULT NULL,
                csrf_token_expires_at TIMESTAMP(6) NULL DEFAULT NULL,
                ip_address VARBINARY(16) DEFAULT NULL,
                last_ip_address VARBINARY(16) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                session_data TEXT DEFAULT NULL COMMENT 'Raw session serialized data',

                -- Audit metadata fields
                created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
                created_by BINARY(16) NOT NULL COMMENT 'UUID of creator',
                created_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP of creator (IPv4/IPv6)',

                updated_at TIMESTAMP(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
                updated_by BINARY(16) DEFAULT NULL COMMENT 'UUID of last updater',
                updated_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP of last updater',

                deleted_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
                deleted_by BINARY(16) DEFAULT NULL COMMENT 'UUID of deleter',
                deleted_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP of deleter',

                last_used_at TIMESTAMP(6) NULL DEFAULT NULL,
                expires_at TIMESTAMP(6) NULL DEFAULT NULL,
                revoked_at TIMESTAMP(6) NULL DEFAULT NULL,
                session_status ENUM('active', 'revoked', 'expired') NOT NULL DEFAULT 'active',

                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',

                CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
                INDEX idx_session_user_id (user_id),
                INDEX idx_session_token (session_token),
                INDEX idx_session_expires_at (expires_at),
                INDEX idx_session_revoked_at (revoked_at),
                INDEX idx_session_last_used_at (last_used_at),
                INDEX idx_session_user_status_expires (user_id, session_status, expires_at)
            ) ENGINE=InnoDB 
              CHARSET=utf8mb4 
              COLLATE=utf8mb4_unicode_ci
            COMMENT='Authentication sessions core table with audit metadata';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS session;");
    }
}
