<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910233000_create_api_rate_limit_table
 *
 * Creates 'api_rate_limit' table for core usage counts.
 * Audit events logged via centralized audit_log table through triggers.
 */
class Migration_20250910233000_create_api_rate_limit_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910233000_create_api_rate_limit_table';
    }

    public function getDescription(): string
    {
        return 'Creates api_rate_limit table for core counters; audit via global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS api_rate_limit (
                id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID PRIMARY KEY',
                user_id CHAR(36) DEFAULT NULL,
                session_id CHAR(64) DEFAULT NULL,
                endpoint VARCHAR(100) NOT NULL,
                user_tier TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'User subscription level',
                request_count INT UNSIGNED NOT NULL DEFAULT 0,
                period_start TIMESTAMP(6) NOT NULL,

                -- Audit metadata fields
                created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'Record creation timestamp',
                created_by BINARY(16) DEFAULT NULL COMMENT 'UUID of creator',
                created_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of creator (IPv4/IPv6)',

                updated_at TIMESTAMP(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6) COMMENT 'Last update timestamp',
                updated_by BINARY(16) DEFAULT NULL COMMENT 'UUID of last updater',
                updated_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of last updater (IPv4/IPv6)',

                deleted_at TIMESTAMP(6) NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
                deleted_by BINARY(16) DEFAULT NULL COMMENT 'UUID of deleter',
                deleted_ip VARBINARY(16) DEFAULT NULL COMMENT 'IP address of deleter (IPv4/IPv6)',

                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',

                CONSTRAINT chk_api_rl_user_session CHECK (
                    (user_id IS NOT NULL AND session_id IS NULL) OR
                    (user_id IS NULL AND session_id IS NOT NULL)
                ),
                CONSTRAINT fk_api_rl_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
                INDEX idx_api_rl_user_endpoint_period (user_id, endpoint, period_start),
                INDEX idx_api_rl_session_endpoint_period (session_id, endpoint, period_start)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Tracks API request counts per user/session with tiered rate limiting';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS api_rate_limit;");
    }
}
