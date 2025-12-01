<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Job\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910233601_create_background_job_audit_trigger
 *
 * Creates audit triggers on 'background_job' table to log changes
 * into centralized global audit_log table, following best practices.
 */
class Migration_20250910233601_create_background_job_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910233601_create_background_job_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on background_job table for insert, update, delete events to global audit_log';
    }

    public function getDependencies(): array
    {
        return ['20250910233600_create_background_job_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if they exist
        $db->query("DROP TRIGGER IF EXISTS trg_audit_background_job_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_background_job_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_background_job_delete;");

        // Create INSERT audit trigger
        $db->query("
            CREATE TRIGGER trg_audit_background_job_insert
            AFTER INSERT ON background_job
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
                ) VALUES (
                    UUID(), 'background_job', NEW.id, 'created', NEW.created_at, NEW.created_by, NEW.created_ip, NEW.version, NULL,
                    JSON_OBJECT(
                        'job_name', NEW.job_name,
                        'payload', NEW.payload,
                        'status', NEW.status,
                        'retries', NEW.retries,
                        'scheduled_at', NEW.scheduled_at,
                        'started_at', NEW.started_at,
                        'completed_at', NEW.completed_at,
                        'created_at', NEW.created_at,
                        'updated_at', NEW.updated_at
                    )
                );
            END
        ");

        // Create UPDATE audit trigger
        $db->query("
            CREATE TRIGGER trg_audit_background_job_update
            AFTER UPDATE ON background_job
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
                ) VALUES (
                    UUID(), 'background_job', NEW.id, 'updated', NEW.updated_at, NEW.updated_by, NEW.updated_ip, NEW.version,
                    JSON_OBJECT(
                        'job_name', OLD.job_name,
                        'payload', OLD.payload,
                        'status', OLD.status,
                        'retries', OLD.retries,
                        'scheduled_at', OLD.scheduled_at,
                        'started_at', OLD.started_at,
                        'completed_at', OLD.completed_at,
                        'created_at', OLD.created_at,
                        'updated_at', OLD.updated_at
                    ),
                    JSON_OBJECT(
                        'job_name', NEW.job_name,
                        'payload', NEW.payload,
                        'status', NEW.status,
                        'retries', NEW.retries,
                        'scheduled_at', NEW.scheduled_at,
                        'started_at', NEW.started_at,
                        'completed_at', NEW.completed_at,
                        'created_at', NEW.created_at,
                        'updated_at', NEW.updated_at
                    )
                );
            END
        ");

        // Create DELETE audit trigger
        $db->query("
            CREATE TRIGGER trg_audit_background_job_delete
            AFTER DELETE ON background_job
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
                ) VALUES (
                    UUID(), 'background_job', OLD.id, 'deleted', OLD.deleted_at, OLD.deleted_by, OLD.deleted_ip, OLD.version,
                    JSON_OBJECT(
                        'job_name', OLD.job_name,
                        'payload', OLD.payload,
                        'status', OLD.status,
                        'retries', OLD.retries,
                        'scheduled_at', OLD.scheduled_at,
                        'started_at', OLD.started_at,
                        'completed_at', OLD.completed_at,
                        'created_at', OLD.created_at,
                        'updated_at', OLD.updated_at
                    ),
                    NULL
                );
            END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        // Drop audit triggers on rollback
        $db->query("DROP TRIGGER IF EXISTS trg_audit_background_job_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_background_job_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_background_job_delete;");
    }
}

