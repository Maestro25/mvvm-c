<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Job\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910233500_create_job_status_table
 *
 * Creates job_status enumeration table.
 */
class Migration_20250910233500_create_job_status_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910233500_create_job_status_table';
    }

    public function getDescription(): string
    {
        return 'Creates background job status enumeration table';
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS job_status (
                id VARCHAR(36) NULL PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                description VARCHAR(255) DEFAULT NULL
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Background job statuses enumeration';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS job_status;");
    }
}
