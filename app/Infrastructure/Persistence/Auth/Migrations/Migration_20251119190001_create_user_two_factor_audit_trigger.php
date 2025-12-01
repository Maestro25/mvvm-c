<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251119190001_create_user_two_factor_audit_trigger
 *
 * Creates audit triggers on 'user_two_factor' table for insert, update, and delete events.
 * Logs comprehensive audit metadata and version into the centralized global audit_log table.
 */
class Migration_20251119190001_create_user_two_factor_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251119190001_create_user_two_factor_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on user_two_factor table for all lifecycle events to global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20251119190000_create_user_two_factor_table'];
    }

    public function up(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_two_factor_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_two_factor_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_two_factor_delete;");

        $db->query("
CREATE TRIGGER trg_audit_user_two_factor_insert
AFTER INSERT ON user_two_factor
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
        'user_two_factor',
        NEW.id,
        'created',
        NEW.created_at,
        NULL,
        NULL,
        NEW.version,
        NULL,
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'type', NEW.type,
            'enabled', NEW.enabled,
            'is_primary', NEW.is_primary,
            'last_verified_at', NEW.last_verified_at,
            'deleted_at', NEW.deleted_at
        )
    );
END;
        ");

        $db->query("
CREATE TRIGGER trg_audit_user_two_factor_update
AFTER UPDATE ON user_two_factor
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
        'user_two_factor',
        NEW.id,
        'updated',
        NEW.updated_at,
        NULL,
        NULL,
        NEW.version,
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'type', OLD.type,
            'enabled', OLD.enabled,
            'is_primary', OLD.is_primary,
            'last_verified_at', OLD.last_verified_at,
            'deleted_at', OLD.deleted_at
        ),
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'type', NEW.type,
            'enabled', NEW.enabled,
            'is_primary', NEW.is_primary,
            'last_verified_at', NEW.last_verified_at,
            'deleted_at', NEW.deleted_at
        )
    );
END;
        ");

        $db->query("
CREATE TRIGGER trg_audit_user_two_factor_delete
AFTER DELETE ON user_two_factor
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
        'user_two_factor',
        OLD.id,
        'deleted',
        COALESCE(OLD.deleted_at, CURRENT_TIMESTAMP(6)),
        NULL,
        NULL,
        OLD.version,
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'type', OLD.type,
            'enabled', OLD.enabled,
            'is_primary', OLD.is_primary,
            'last_verified_at', OLD.last_verified_at,
            'deleted_at', OLD.deleted_at
        ),
        NULL
    );
END;
        ");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_two_factor_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_two_factor_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_two_factor_delete;");
    }
}
