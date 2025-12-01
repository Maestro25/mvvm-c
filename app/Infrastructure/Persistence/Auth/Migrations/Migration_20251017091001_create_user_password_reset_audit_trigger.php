<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251017091001_create_user_password_reset_audit_trigger
 *
 * Creates audit triggers on 'user_password_reset' table to log detailed insert, update, and delete events
 * into the global audit_log table for robust auditing.
 */
class Migration_20251017091001_create_user_password_reset_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251017091001_create_user_password_reset_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on user_password_reset table logging changes into global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20251017091000_create_user_password_reset_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers to avoid conflicts
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_password_reset_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_password_reset_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_password_reset_delete;");

        // Insert audit trigger
        $db->query("
CREATE TRIGGER trg_audit_user_password_reset_insert
AFTER INSERT ON user_password_reset
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action,
        changed_at, changed_by, changed_ip, version,
        old_value, new_value
    ) VALUES (
        UUID(),
        'user_password_reset',
        NEW.user_id,
        'created',
        CURRENT_TIMESTAMP(6),
        USER(),
        NULL,
        NEW.version,
        NULL,
        JSON_OBJECT(
            'reset_token', NEW.reset_token,
            'reset_token_expires_at', NEW.reset_token_expires_at,
            'used_at', NEW.used_at,
            'revoked', NEW.revoked,
            'revoked_at', NEW.revoked_at,
            'revoked_by', NEW.revoked_by,
            'reason_for_revocation', NEW.reason_for_revocation,
            'token_hash_version', NEW.token_hash_version,
            'device_info', NEW.device_info,
            'origin', NEW.origin
        )
    );
END;
        ");

        // Update audit trigger
        $db->query("
CREATE TRIGGER trg_audit_user_password_reset_update
AFTER UPDATE ON user_password_reset
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action,
        changed_at, changed_by, changed_ip, version,
        old_value, new_value
    ) VALUES (
        UUID(),
        'user_password_reset',
        NEW.user_id,
        'updated',
        CURRENT_TIMESTAMP(6),
        USER(),
        NULL,
        NEW.version,
        JSON_OBJECT(
            'reset_token', OLD.reset_token,
            'reset_token_expires_at', OLD.reset_token_expires_at,
            'used_at', OLD.used_at,
            'revoked', OLD.revoked,
            'revoked_at', OLD.revoked_at,
            'revoked_by', OLD.revoked_by,
            'reason_for_revocation', OLD.reason_for_revocation,
            'token_hash_version', OLD.token_hash_version,
            'device_info', OLD.device_info,
            'origin', OLD.origin
        ),
        JSON_OBJECT(
            'reset_token', NEW.reset_token,
            'reset_token_expires_at', NEW.reset_token_expires_at,
            'used_at', NEW.used_at,
            'revoked', NEW.revoked,
            'revoked_at', NEW.revoked_at,
            'revoked_by', NEW.revoked_by,
            'reason_for_revocation', NEW.reason_for_revocation,
            'token_hash_version', NEW.token_hash_version,
            'device_info', NEW.device_info,
            'origin', NEW.origin
        )
    );
END;
        ");

        // Delete audit trigger
        $db->query("
CREATE TRIGGER trg_audit_user_password_reset_delete
AFTER DELETE ON user_password_reset
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action,
        changed_at, changed_by, changed_ip, version,
        old_value, new_value
    ) VALUES (
        UUID(),
        'user_password_reset',
        OLD.user_id,
        'deleted',
        CURRENT_TIMESTAMP(6),
        USER(),
        NULL,
        OLD.version,
        JSON_OBJECT(
            'reset_token', OLD.reset_token,
            'reset_token_expires_at', OLD.reset_token_expires_at,
            'used_at', OLD.used_at,
            'revoked', OLD.revoked,
            'revoked_at', OLD.revoked_at,
            'revoked_by', OLD.revoked_by,
            'reason_for_revocation', OLD.reason_for_revocation,
            'token_hash_version', OLD.token_hash_version,
            'device_info', OLD.device_info,
            'origin', OLD.origin
        ),
        NULL
    );
END;
        ");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_password_reset_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_password_reset_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_password_reset_delete;");
    }
}
