<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910223001_create_password_history_audit_triggers
 *
 * Creates audit triggers on 'password_history' table for insert, update, and delete events
 * logging to centralized global audit_log table with consistent metadata and versioning.
 */
class Migration_20250910223001_create_password_history_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910223001_create_password_history_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on password_history table for insert, update and delete events to global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20250910223000_create_password_history_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if they exist, preventing conflicts
        $db->query("DROP TRIGGER IF EXISTS trg_audit_password_history_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_password_history_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_password_history_delete;");

        // Insert audit trigger
        $db->query("
CREATE TRIGGER trg_audit_password_history_insert
AFTER INSERT ON password_history
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id,
        entity_type,
        entity_id,
        action,
        changed_at,
        changed_by,
        changed_ip,
        version,
        old_value,
        new_value
    ) VALUES (
        UUID(),
        'password_history',
        NEW.id,
        'created',
        NEW.created_at,
        NULL,
        NULL,
        NEW.version,
        NULL,
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'password_hash', NEW.password_hash,
            'created_at', NEW.created_at
        )
    );
END
        ");

        // Update audit trigger
        $db->query("
CREATE TRIGGER trg_audit_password_history_update
AFTER UPDATE ON password_history
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id,
        entity_type,
        entity_id,
        action,
        changed_at,
        changed_by,
        changed_ip,
        version,
        old_value,
        new_value
    ) VALUES (
        UUID(),
        'password_history',
        NEW.id,
        'updated',
        CURRENT_TIMESTAMP(6),
        NULL,
        NULL,
        NEW.version,
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'password_hash', OLD.password_hash,
            'created_at', OLD.created_at
        ),
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'password_hash', NEW.password_hash,
            'created_at', NEW.created_at
        )
    );
END
        ");

        // Delete audit trigger
        $db->query("
CREATE TRIGGER trg_audit_password_history_delete
AFTER DELETE ON password_history
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id,
        entity_type,
        entity_id,
        action,
        changed_at,
        changed_by,
        changed_ip,
        version,
        old_value,
        new_value
    ) VALUES (
        UUID(),
        'password_history',
        OLD.id,
        'deleted',
        CURRENT_TIMESTAMP(6),
        NULL,
        NULL,
        OLD.version,
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'password_hash', OLD.password_hash,
            'created_at', OLD.created_at
        ),
        NULL
    );
END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        // Drop audit triggers on rollback
        $db->query("DROP TRIGGER IF EXISTS trg_audit_password_history_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_password_history_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_password_history_delete;");
    }
}
