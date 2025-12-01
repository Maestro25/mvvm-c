<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251017093001_create_user_remember_me_audit_trigger
 *
 * Creates audit triggers for INSERT, UPDATE, DELETE on user_remember_me table,
 * logging all changes to the global audit_log table following MariaDB best practices.
 */
class Migration_20251017093001_create_user_remember_me_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251017093001_create_user_remember_me_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on user_remember_me table for global audit_log.';
    }

    public function getDependencies(): array
    {
        return ['20251017093000_create_user_remember_me_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if any
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_remember_me_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_remember_me_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_remember_me_delete;");

        // INSERT trigger
        $db->query("
CREATE TRIGGER trg_audit_user_remember_me_insert
AFTER INSERT ON user_remember_me
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, old_value, new_value, version
    ) VALUES (
        UUID(), 'user_remember_me', NEW.user_id, 'created', CURRENT_TIMESTAMP(6), USER(), NULL,
        JSON_OBJECT(
            'remember_me_token', NEW.remember_me_token,
            'remember_me_token_expires_at', NEW.remember_me_token_expires_at,
            'revoked', NEW.revoked,
            'device_info', NEW.device_info,
            'origin', NEW.origin,
            'used_at', NEW.used_at,
            'revoked_at', NEW.revoked_at,
            'revoked_by', NEW.revoked_by,
            'reason_for_revocation', NEW.reason_for_revocation,
            'token_hash_version', NEW.token_hash_version,
            'created_by', NEW.created_by,
            'created_ip', HEX(NEW.created_ip),
            'updated_by', NEW.updated_by,
            'updated_ip', HEX(NEW.updated_ip)
        ),
        1
    );
END
        ");

        // UPDATE trigger
        $db->query("
CREATE TRIGGER trg_audit_user_remember_me_update
AFTER UPDATE ON user_remember_me
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, old_value, new_value, version
    ) VALUES (
        UUID(), 'user_remember_me', NEW.user_id, 'updated', CURRENT_TIMESTAMP(6), USER(),
        JSON_OBJECT(
            'remember_me_token', OLD.remember_me_token,
            'remember_me_token_expires_at', OLD.remember_me_token_expires_at,
            'revoked', OLD.revoked,
            'device_info', OLD.device_info,
            'origin', OLD.origin,
            'used_at', OLD.used_at,
            'revoked_at', OLD.revoked_at,
            'revoked_by', OLD.revoked_by,
            'reason_for_revocation', OLD.reason_for_revocation,
            'token_hash_version', OLD.token_hash_version,
            'created_by', OLD.created_by,
            'created_ip', HEX(OLD.created_ip),
            'updated_by', OLD.updated_by,
            'updated_ip', HEX(OLD.updated_ip)
        ),
        JSON_OBJECT(
            'remember_me_token', NEW.remember_me_token,
            'remember_me_token_expires_at', NEW.remember_me_token_expires_at,
            'revoked', NEW.revoked,
            'device_info', NEW.device_info,
            'origin', NEW.origin,
            'used_at', NEW.used_at,
            'revoked_at', NEW.revoked_at,
            'revoked_by', NEW.revoked_by,
            'reason_for_revocation', NEW.reason_for_revocation,
            'token_hash_version', NEW.token_hash_version,
            'created_by', NEW.created_by,
            'created_ip', HEX(NEW.created_ip),
            'updated_by', NEW.updated_by,
            'updated_ip', HEX(NEW.updated_ip)
        ),
        1
    );
END
        ");

        // DELETE trigger
        $db->query("
CREATE TRIGGER trg_audit_user_remember_me_delete
AFTER DELETE ON user_remember_me
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at, changed_by, old_value, new_value, version
    ) VALUES (
        UUID(), 'user_remember_me', OLD.user_id, 'deleted', CURRENT_TIMESTAMP(6), USER(),
        JSON_OBJECT(
            'remember_me_token', OLD.remember_me_token,
            'remember_me_token_expires_at', OLD.remember_me_token_expires_at,
            'revoked', OLD.revoked,
            'device_info', OLD.device_info,
            'origin', OLD.origin,
            'used_at', OLD.used_at,
            'revoked_at', OLD.revoked_at,
            'revoked_by', OLD.revoked_by,
            'reason_for_revocation', OLD.reason_for_revocation,
            'token_hash_version', OLD.token_hash_version,
            'created_by', OLD.created_by,
            'created_ip', HEX(OLD.created_ip),
            'updated_by', OLD.updated_by,
            'updated_ip', HEX(OLD.updated_ip)
        ),
        NULL,
        1
    );
END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_remember_me_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_remember_me_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_remember_me_delete;");
    }
}
