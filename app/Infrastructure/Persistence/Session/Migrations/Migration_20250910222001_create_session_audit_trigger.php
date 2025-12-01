<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Session\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910222001_create_session_audit_trigger
 *
 * Creates audit triggers on 'session' table to log insert, update, delete events
 * into the centralized global audit_log table, including version and full audit metadata.
 */
class Migration_20250910222001_create_session_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910222001_create_session_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on session table for insert, update, delete events with full audit metadata to global audit_log';
    }

    public function getDependencies(): array
    {
        return ['20250910222000_create_session_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if any before creating new ones
        $db->query("DROP TRIGGER IF EXISTS trg_audit_session_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_session_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_session_delete;");

        // Insert audit trigger
        $db->query("
CREATE TRIGGER trg_audit_session_insert
AFTER INSERT ON session
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
    ) VALUES (
        UUID(), 'session', NEW.id, 'created', NEW.created_at, NEW.created_by, NEW.created_ip, NEW.version, NULL,
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'session_token', NEW.session_token,
            'session_token_expires_at', NEW.session_token_expires_at,
            'refresh_token', NEW.refresh_token,
            'refresh_token_expires_at', NEW.refresh_token_expires_at,
            'csrf_token', NEW.csrf_token,
            'csrf_token_expires_at', NEW.csrf_token_expires_at,
            'ip_address', HEX(NEW.ip_address),
            'last_ip_address', HEX(NEW.last_ip_address),
            'user_agent', NEW.user_agent,
            'session_status', NEW.session_status,
            'created_at', NEW.created_at
        )
    );
END
        ");

        // Update audit trigger
        $db->query("
CREATE TRIGGER trg_audit_session_update
AFTER UPDATE ON session
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
    ) VALUES (
        UUID(), 'session', NEW.id, 'updated', NEW.updated_at, NEW.updated_by, NEW.updated_ip, NEW.version,
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'session_token', OLD.session_token,
            'session_token_expires_at', OLD.session_token_expires_at,
            'refresh_token', OLD.refresh_token,
            'refresh_token_expires_at', OLD.refresh_token_expires_at,
            'csrf_token', OLD.csrf_token,
            'csrf_token_expires_at', OLD.csrf_token_expires_at,
            'ip_address', HEX(OLD.ip_address),
            'last_ip_address', HEX(OLD.last_ip_address),
            'user_agent', OLD.user_agent,
            'session_status', OLD.session_status,
            'updated_at', OLD.updated_at
        ),
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'session_token', NEW.session_token,
            'session_token_expires_at', NEW.session_token_expires_at,
            'refresh_token', NEW.refresh_token,
            'refresh_token_expires_at', NEW.refresh_token_expires_at,
            'csrf_token', NEW.csrf_token,
            'csrf_token_expires_at', NEW.csrf_token_expires_at,
            'ip_address', HEX(NEW.ip_address),
            'last_ip_address', HEX(NEW.last_ip_address),
            'user_agent', NEW.user_agent,
            'session_status', NEW.session_status,
            'updated_at', NEW.updated_at
        )
    );
END
        ");

        // Delete audit trigger
        $db->query("
CREATE TRIGGER trg_audit_session_delete
AFTER DELETE ON session
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
    ) VALUES (
        UUID(), 'session', OLD.id, 'deleted', OLD.deleted_at, OLD.deleted_by, OLD.deleted_ip, OLD.version,
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'session_token', OLD.session_token,
            'session_token_expires_at', OLD.session_token_expires_at,
            'refresh_token', OLD.refresh_token,
            'refresh_token_expires_at', OLD.refresh_token_expires_at,
            'csrf_token', OLD.csrf_token,
            'csrf_token_expires_at', OLD.csrf_token_expires_at,
            'ip_address', HEX(OLD.ip_address),
            'last_ip_address', HEX(OLD.last_ip_address),
            'user_agent', OLD.user_agent,
            'session_status', OLD.session_status,
            'deleted_at', OLD.deleted_at
        ),
        NULL
    );
END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_session_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_session_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_session_delete;");
    }
}
