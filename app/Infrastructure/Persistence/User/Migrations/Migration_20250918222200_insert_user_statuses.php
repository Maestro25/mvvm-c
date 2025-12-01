<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;
use App\Domain\User\Enums\UserStatus;

/**
 * Migration_20250918222200_insert_user_statuses
 *
 * Inserts enum values from Status into user_status lookup table.
 */
class Migration_20250918222200_insert_user_statuses implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250918222200_insert_user_statuses';
    }

    public function getDescription(): string
    {
        return 'Inserts user statuses from Status enum into user_status table';
    }

    public function getDependencies(): array
    {
        return ['20250910221000_create_user_status_table']; // Depends on the table creation migration
    }

    public function up(SafeMySQL $db): void
    {
        $statuses = UserStatus::values();

        foreach ($statuses as $status) {
            // Insert with ON DUPLICATE KEY to avoid duplicates if migration runs multiple times
            $db->query(
                "INSERT INTO user_status (id, name) VALUES (?s, ?s) ON DUPLICATE KEY UPDATE name = VALUES(name)",
                $status,
                ucfirst($status)
            );
        }
    }

    public function down(SafeMySQL $db): void
    {
        $statuses = UserStatus::values();

        foreach ($statuses as $status) {
            $db->query("DELETE FROM user_status WHERE id = ?s", $status);
        }
    }
}
