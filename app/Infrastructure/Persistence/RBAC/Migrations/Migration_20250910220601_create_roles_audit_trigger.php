<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\RBAC\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910220601_create_roles_audit_triggers
 *
 * Creates audit triggers on 'roles' table to log insert, update, and delete events
 * into the centralized global audit_log table with improved audit metadata.
 */
class Migration_20250910220601_create_roles_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910220601_create_roles_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on roles table for insert, update, delete events to global audit_log with user/ip metadata';
    }

    public function getDependencies(): array
    {
        return ['20250910220600_create_roles_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if they exist
        $db->query("DROP TRIGGER IF EXISTS trg_audit_roles_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_roles_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_roles_delete;");

        // Create INSERT audit trigger
        $db->query("
CREATE TRIGGER trg_audit_roles_insert
AFTER INSERT ON roles
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
        old_value,
        new_value,
        version
    ) VALUES (
        UUID(),
        'roles',
        NEW.id,
        'created',
        NEW.created_at,
        NEW.created_by,
        NEW.created_ip,
        NULL,
        JSON_OBJECT(
            'name', NEW.name,
            'description', NEW.description
        ),
        NEW.version
    );
END
        ");

        // Create UPDATE audit trigger
        $db->query("
CREATE TRIGGER trg_audit_roles_update
AFTER UPDATE ON roles
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
        old_value,
        new_value,
        version
    ) VALUES (
        UUID(),
        'roles',
        NEW.id,
        'updated',
        NEW.updated_at,
        NEW.updated_by,
        NEW.updated_ip,
        JSON_OBJECT(
            'name', OLD.name,
            'description', OLD.description
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'description', NEW.description
        ),
        NEW.version
    );
END
        ");

        // Create DELETE audit trigger
        $db->query("
CREATE TRIGGER trg_audit_roles_delete
AFTER DELETE ON roles
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
        old_value,
        new_value,
        version
    ) VALUES (
        UUID(),
        'roles',
        OLD.id,
        'deleted',
        OLD.deleted_at,
        OLD.deleted_by,
        OLD.deleted_ip,
        JSON_OBJECT(
            'name', OLD.name,
            'description', OLD.description
        ),
        NULL,
        OLD.version
    );
END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        // Drop audit triggers on rollback
        $db->query("DROP TRIGGER IF EXISTS trg_audit_roles_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_roles_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_roles_delete;");
    }
}
