<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations;

use SafeMySQL;
use Throwable;
use RuntimeException;
use LogicException;

/**
 * Interface MigrationInterface
 * Defines contract for each migration.
 */
interface MigrationInterface
{
    public function getVersion(): string;
    public function getDescription(): string;
    public function getDependencies(): array; // explicit dependency versions

    /**
     * Apply migration (schema or data changes).
     * Throws on failure.
     */
    public function up(SafeMySQL $db): void;

    /**
     * Revert migration.
     * Throws on failure.
     */
    public function down(SafeMySQL $db): void;
}