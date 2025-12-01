<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20251017092001_create_user_email_verification_audit_trigger
 *
 * Creates audit triggers on 'user_email_verification' table to log insert, update, delete events
 * into centralized global audit_log maintaining full versioning and metadata.
 */
class Migration_20251017092001_create_user_email_verification_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20251017092001_create_user_email_verification_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers for user_email_verification table logging changes to global audit_log';
    }

    public function getDependencies(): array
    {
        return ['20251017092000_create_user_email_verification_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop triggers if they already exist
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_email_verification_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_email_verification_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_email_verification_delete;");

        // Insert trigger
        $db->query("
CREATE TRIGGER trg_audit_user_email_verification_insert
AFTER INSERT ON user_email_verification
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at,
        changed_by, changed_ip, old_value, new_value, version
    ) VALUES (
        UUID(), 'user_email_verification', NEW.user_id, 'created',
        CURRENT_TIMESTAMP(6), USER(), NULL, NULL,
        JSON_OBJECT(
            'verification_token', NEW.verification_token,
            'verification_token_expires_at', NEW.verification_token_expires_at,
            'used_at', NEW.used_at,
            'revoked', NEW.revoked,
            'revoked_at', NEW.revoked_at,
            'revoked_by', NEW.revoked_by,
            'reason_for_revocation', NEW.reason_for_revocation,
            'token_hash_version', NEW.token_hash_version,
            'device_info', NEW.device_info,
            'origin', NEW.origin
        ),
        NEW.version
    );
END;
        ");

        // Update trigger
        $db->query("
CREATE TRIGGER trg_audit_user_email_verification_update
AFTER UPDATE ON user_email_verification
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at,
        changed_by, changed_ip, old_value, new_value, version
    ) VALUES (
        UUID(), 'user_email_verification', NEW.user_id, 'updated',
        CURRENT_TIMESTAMP(6), USER(), NULL,
        JSON_OBJECT(
            'verification_token', OLD.verification_token,
            'verification_token_expires_at', OLD.verification_token_expires_at,
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
            'verification_token', NEW.verification_token,
            'verification_token_expires_at', NEW.verification_token_expires_at,
            'used_at', NEW.used_at,
            'revoked', NEW.revoked,
            'revoked_at', NEW.revoked_at,
            'revoked_by', NEW.revoked_by,
            'reason_for_revocation', NEW.reason_for_revocation,
            'token_hash_version', NEW.token_hash_version,
            'device_info', NEW.device_info,
            'origin', NEW.origin
        ),
        NEW.version
    );
END;
        ");

        // Delete trigger
        $db->query("
CREATE TRIGGER trg_audit_user_email_verification_delete
AFTER DELETE ON user_email_verification
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        audit_id, entity_type, entity_id, action, changed_at,
        changed_by, changed_ip, old_value, new_value, version
    ) VALUES (
        UUID(), 'user_email_verification', OLD.user_id, 'deleted',
        CURRENT_TIMESTAMP(6), USER(), NULL,
        JSON_OBJECT(
            'verification_token', OLD.verification_token,
            'verification_token_expires_at', OLD.verification_token_expires_at,
            'used_at', OLD.used_at,
            'revoked', OLD.revoked,
            'revoked_at', OLD.revoked_at,
            'revoked_by', OLD.revoked_by,
            'reason_for_revocation', OLD.reason_for_revocation,
            'token_hash_version', OLD.token_hash_version,
            'device_info', OLD.device_info,
            'origin', OLD.origin
        ),
        NULL,
        OLD.version
    );
END;
        ");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_email_verification_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_email_verification_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_email_verification_delete;");
    }
}
