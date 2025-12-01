<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250909180000_create_global_audit_log_table
 *
 * Creates a global audit_log table for polymorphic audit entries across all entities.
 * This unified audit log enables detailed tracing of all create, update, and delete events.
 */
class Migration_20250909180000_create_global_audit_log_table implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250909180000_create_global_audit_log_table';
    }

    public function getDescription(): string
    {
        return 'Creates global audit_log table for tracking audited entity changes across all tables.';
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS audit_log (
                audit_id CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID for audit entry',
                entity_type VARCHAR(100) NOT NULL COMMENT 'Audited entity/table name',
                entity_id CHAR(36) NOT NULL COMMENT 'UUID of the audited record',
                action ENUM('created', 'updated', 'deleted') NOT NULL COMMENT 'Type of change event',
                changed_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'Change event timestamp',
                changed_by CHAR(36) NOT NULL COMMENT 'UUID of user who made change',
                changed_ip VARBINARY(16) DEFAULT NULL COMMENT 'Actor IP address (IPv4/IPv6)',
                version INT NOT NULL COMMENT 'Record version for optimistic concurrency control',
                change_reason VARCHAR(255) DEFAULT NULL COMMENT 'Optional reason for change',
                old_value JSON DEFAULT NULL COMMENT 'Previous record state snapshot',
                new_value JSON DEFAULT NULL COMMENT 'New record state snapshot',
                
                INDEX idx_audit_entity (entity_type, entity_id),
                INDEX idx_audit_action_changed_at (action, changed_at)
            ) ENGINE=InnoDB
              CHARSET=utf8mb4
              COLLATE=utf8mb4_unicode_ci
            COMMENT='Global audit log capturing changes across all audited entities';
        SQL);
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TABLE IF EXISTS audit_log;");
    }
}
