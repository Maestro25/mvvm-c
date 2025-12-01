<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910223000_create_password_history_table
 *
 * Creates the 'password_history' table with core password hashes,
 * including version field for optimistic concurrency.
 * Audit events recorded globally via triggers.
 */
class Migration_20250910223000_create_password_history_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910223000_create_password_history_table';
    }

    public function getDescription(): string
    {
        return 'Creates password_history table for storing core password hashes.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS password_history (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id CHAR(36) NOT NULL COMMENT 'FK to user(id)',
                password_hash VARCHAR(255) NOT NULL COMMENT 'Stored password hash',
                created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
                version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Optimistic concurrency/version control',

                CONSTRAINT fk_password_history_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
                INDEX idx_password_history_user_id (user_id),
                INDEX idx_password_history_created_at (created_at)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Stores historical password hashes for users to prevent reuse';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS password_history;");
    }
}
