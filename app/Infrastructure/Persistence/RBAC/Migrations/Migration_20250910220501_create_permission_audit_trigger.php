<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\RBAC\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910220501_create_permission_audit_trigger
 *
 * Creates audit triggers on 'permission' table for insert, update, delete events
 * logging to centralized global audit_log with consistent fields.
 */
class Migration_20250910220501_create_permission_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910220501_create_permission_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on permission table for insert, update, delete events to global audit_log';
    }

    public function getDependencies(): array
    {
        return ['20250910220500_create_permission_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if they exist
        $db->query("DROP TRIGGER IF EXISTS trg_audit_permission_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_permission_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_permission_delete;");

        // Insert audit trigger
        $db->query("
CREATE TRIGGER trg_audit_permission_insert
AFTER INSERT ON permission
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action,
        changed_at, changed_by, changed_ip, version,
        old_value, new_value
    ) VALUES (
        UUID(), 'permission', NEW.id, 'created',
        NEW.created_at, NEW.created_by, NEW.created_ip, NEW.version,
        NULL,
        JSON_OBJECT(
            'name', NEW.name,
            'description', NEW.description
        )
    );
END
");

        // Update audit trigger
        $db->query("
CREATE TRIGGER trg_audit_permission_update
AFTER UPDATE ON permission
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action,
        changed_at, changed_by, changed_ip, version,
        old_value, new_value
    ) VALUES (
        UUID(), 'permission', NEW.id, 'updated',
        NEW.updated_at, NEW.updated_by, NEW.updated_ip, NEW.version,
        JSON_OBJECT(
            'name', OLD.name,
            'description', OLD.description
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'description', NEW.description
        )
    );
END
");

        // Delete audit trigger
        $db->query("
CREATE TRIGGER trg_audit_permission_delete
AFTER DELETE ON permission
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action,
        changed_at, changed_by, changed_ip, version,
        old_value, new_value
    ) VALUES (
        UUID(), 'permission', OLD.id, 'deleted',
        OLD.deleted_at, OLD.deleted_by, OLD.deleted_ip, OLD.version,
        JSON_OBJECT(
            'name', OLD.name,
            'description', OLD.description
        ),
        NULL
    );
END
");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_permission_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_permission_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_permission_delete;");
    }
}
