<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251017090001_create_user_auth_audit_trigger
 *
 * Creates audit triggers on 'user_auth' table for insert, update, and delete events.
 * Audit data consistently logged to global audit_log with version and audit metadata.
 */
class Migration_20251017090001_create_user_auth_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251017090001_create_user_auth_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on user_auth table for insert, update, delete events into global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20251017090000_create_user_auth_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers to prevent conflicts
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_auth_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_auth_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_auth_delete;");

        // INSERT audit trigger
        $db->query("
CREATE TRIGGER trg_audit_user_auth_insert
AFTER INSERT ON user_auth
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
        'user_auth',
        NEW.user_id,
        'created',
        COALESCE(NEW.last_login_at, CURRENT_TIMESTAMP(6)),
        NULL, -- Populate changed_by from application context if available
        NULL,
        NEW.version,
        NULL,
        JSON_OBJECT(
            'failed_login_attempts', NEW.failed_login_attempts,
            'last_failed_login_at', NEW.last_failed_login_at,
            'locked_until', NEW.locked_until,
            'last_login_at', NEW.last_login_at,
            'password_hash_version', NEW.password_hash_version,
            'last_password_change_at', NEW.last_password_change_at,
            'two_factor_enabled', NEW.two_factor_enabled,
            'two_factor_last_verified_at', NEW.two_factor_last_verified_at,
            'deleted_at', NEW.deleted_at
        )
    );
END;
        ");

        // UPDATE audit trigger
        $db->query("
CREATE TRIGGER trg_audit_user_auth_update
AFTER UPDATE ON user_auth
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
        'user_auth',
        NEW.user_id,
        'updated',
        CURRENT_TIMESTAMP(6),
        NULL, -- Populate changed_by if possible
        NULL,
        NEW.version,
        JSON_OBJECT(
            'failed_login_attempts', OLD.failed_login_attempts,
            'last_failed_login_at', OLD.last_failed_login_at,
            'locked_until', OLD.locked_until,
            'last_login_at', OLD.last_login_at,
            'password_hash_version', OLD.password_hash_version,
            'last_password_change_at', OLD.last_password_change_at,
            'two_factor_enabled', OLD.two_factor_enabled,
            'two_factor_last_verified_at', OLD.two_factor_last_verified_at,
            'deleted_at', OLD.deleted_at
        ),
        JSON_OBJECT(
            'failed_login_attempts', NEW.failed_login_attempts,
            'last_failed_login_at', NEW.last_failed_login_at,
            'locked_until', NEW.locked_until,
            'last_login_at', NEW.last_login_at,
            'password_hash_version', NEW.password_hash_version,
            'last_password_change_at', NEW.last_password_change_at,
            'two_factor_enabled', NEW.two_factor_enabled,
            'two_factor_last_verified_at', NEW.two_factor_last_verified_at,
            'deleted_at', NEW.deleted_at
        )
    );
END;
        ");

        // DELETE audit trigger
        $db->query("
CREATE TRIGGER trg_audit_user_auth_delete
AFTER DELETE ON user_auth
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
        'user_auth',
        OLD.user_id,
        'deleted',
        COALESCE(OLD.deleted_at, CURRENT_TIMESTAMP(6)),
        NULL,
        NULL,
        OLD.version,
        JSON_OBJECT(
            'failed_login_attempts', OLD.failed_login_attempts,
            'last_failed_login_at', OLD.last_failed_login_at,
            'locked_until', OLD.locked_until,
            'last_login_at', OLD.last_login_at,
            'password_hash_version', OLD.password_hash_version,
            'last_password_change_at', OLD.last_password_change_at,
            'two_factor_enabled', OLD.two_factor_enabled,
            'two_factor_last_verified_at', OLD.two_factor_last_verified_at,
            'deleted_at', OLD.deleted_at
        ),
        NULL
    );
END;
        ");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_auth_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_auth_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_auth_delete;");
    }
}
