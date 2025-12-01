<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations\Dev;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration [version]_[name]
 *
 * [Brief description]
 */
class Migration_YYYYMMDDHHMMSS_description implements MigrationInterface
{
    // Return your unique version string: timestamp_suffix
    public function getVersion(): string
    {
        return '[version]_[name]';
    }

    // Human-readable description
    public function getDescription(): string
    {
        return '[Brief description]';
    }

    // List of prerequisite migration versions if any
    public function getDependencies(): array
    {
        return [];
    }

    // Run migration SQL here - apply changes
    public function up(SafeMySQL $db): void
    {
        // For example, create a table
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS example (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        SQL);
    }

    // Revert the changes made in `up`
    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS example;");
    }
}
