<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910233001_create_api_rate_limit_audit_triggers
 *
 * Creates audit triggers on the 'api_rate_limit' table to log
 * insert, update, and delete events into a centralized global audit_log table.
 * Uses one trigger per event type, compliant with MariaDB syntax and best practices.
 */
class Migration_20250910233001_create_api_rate_limit_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910233001_create_api_rate_limit_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on api_rate_limit table for insert, update, delete events to global audit_log';
    }

    public function getDependencies(): array
    {
        return ['20250910233000_create_api_rate_limit_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if they exist
        $db->query("DROP TRIGGER IF EXISTS trg_audit_api_rate_limit_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_api_rate_limit_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_api_rate_limit_delete;");

        // Insert trigger
        $db->query("
CREATE TRIGGER trg_audit_api_rate_limit_insert
AFTER INSERT ON api_rate_limit
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, old_value, new_value, version
    ) VALUES (
        UUID(), 'api_rate_limit', NEW.id, 'created', CURRENT_TIMESTAMP(6), USER(),
        NULL,
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'session_id', NEW.session_id,
            'endpoint', NEW.endpoint,
            'user_tier', NEW.user_tier,
            'request_count', NEW.request_count,
            'period_start', DATE_FORMAT(NEW.period_start, '%Y-%m-%d %H:%i:%s.%f')
        ),
        1
    );
END
        ");

        // Update trigger
        $db->query("
CREATE TRIGGER trg_audit_api_rate_limit_update
AFTER UPDATE ON api_rate_limit
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, old_value, new_value, version
    ) VALUES (
        UUID(), 'api_rate_limit', NEW.id, 'updated', CURRENT_TIMESTAMP(6), USER(),
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'session_id', OLD.session_id,
            'endpoint', OLD.endpoint,
            'user_tier', OLD.user_tier,
            'request_count', OLD.request_count,
            'period_start', DATE_FORMAT(OLD.period_start, '%Y-%m-%d %H:%i:%s.%f')
        ),
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'session_id', NEW.session_id,
            'endpoint', NEW.endpoint,
            'user_tier', NEW.user_tier,
            'request_count', NEW.request_count,
            'period_start', DATE_FORMAT(NEW.period_start, '%Y-%m-%d %H:%i:%s.%f')
        ),
        1
    );
END
        ");

        // Delete trigger
        $db->query("
CREATE TRIGGER trg_audit_api_rate_limit_delete
AFTER DELETE ON api_rate_limit
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, old_value, new_value, version
    ) VALUES (
        UUID(), 'api_rate_limit', OLD.id, 'deleted', CURRENT_TIMESTAMP(6), USER(),
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'session_id', OLD.session_id,
            'endpoint', OLD.endpoint,
            'user_tier', OLD.user_tier,
            'request_count', OLD.request_count,
            'period_start', DATE_FORMAT(OLD.period_start, '%Y-%m-%d %H:%i:%s.%f')
        ),
        NULL,
        1
    );
END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        // Drop all audit triggers on rollback
        $db->query("DROP TRIGGER IF EXISTS trg_audit_api_rate_limit_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_api_rate_limit_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_api_rate_limit_delete;");
    }
}
