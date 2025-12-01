<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250930210001_create_user_profile_audit_trigger
 *
 * Creates audit triggers on 'user_profile' table that log all changes
 * into the centralized global audit_log table with full audit metadata and versioning.
 */
class Migration_20250930210001_create_user_profile_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250930210001_create_user_profile_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on user_profile table for insert, update, delete events to global audit_log';
    }

    public function getDependencies(): array
    {
        return ['20250930210000_create_user_profile_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if any
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_profile_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_profile_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_profile_delete;");

        // Insert trigger
        $db->query("
            CREATE TRIGGER trg_audit_user_profile_insert
            AFTER INSERT ON user_profile
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action,
                    changed_at, changed_by, changed_ip, version,
                    old_value, new_value
                ) VALUES (
                    UUID(), 'user_profile', NEW.user_id, 'created',
                    NEW.created_at, NEW.created_by, NEW.created_ip, NEW.version,
                    NULL,
                    JSON_OBJECT(
                        'first_name', NEW.first_name,
                        'last_name', NEW.last_name,
                        'phone', NEW.phone,
                        'address_line1', NEW.address_line1,
                        'address_line2', NEW.address_line2,
                        'city', NEW.city,
                        'state', NEW.state,
                        'postal_code', NEW.postal_code,
                        'country', NEW.country,
                        'profile_picture', NEW.profile_picture,
                        'preferences', NEW.preferences,
                        'gender', NEW.gender
                    )
                );
            END
        ");

        // Update trigger
        $db->query("
            CREATE TRIGGER trg_audit_user_profile_update
            AFTER UPDATE ON user_profile
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action,
                    changed_at, changed_by, changed_ip, version,
                    old_value, new_value
                ) VALUES (
                    UUID(), 'user_profile', NEW.user_id, 'updated',
                    NEW.updated_at, NEW.updated_by, NEW.updated_ip, NEW.version,
                    JSON_OBJECT(
                        'first_name', OLD.first_name,
                        'last_name', OLD.last_name,
                        'phone', OLD.phone,
                        'address_line1', OLD.address_line1,
                        'address_line2', OLD.address_line2,
                        'city', OLD.city,
                        'state', OLD.state,
                        'postal_code', OLD.postal_code,
                        'country', OLD.country,
                        'profile_picture', OLD.profile_picture,
                        'preferences', OLD.preferences,
                        'gender', OLD.gender
                    ),
                    JSON_OBJECT(
                        'first_name', NEW.first_name,
                        'last_name', NEW.last_name,
                        'phone', NEW.phone,
                        'address_line1', NEW.address_line1,
                        'address_line2', NEW.address_line2,
                        'city', NEW.city,
                        'state', NEW.state,
                        'postal_code', NEW.postal_code,
                        'country', NEW.country,
                        'profile_picture', NEW.profile_picture,
                        'preferences', NEW.preferences,
                        'gender', NEW.gender
                    )
                );
            END
        ");

        // Delete trigger
        $db->query("
            CREATE TRIGGER trg_audit_user_profile_delete
            AFTER DELETE ON user_profile
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action,
                    changed_at, changed_by, changed_ip, version,
                    old_value, new_value
                ) VALUES (
                    UUID(), 'user_profile', OLD.user_id, 'deleted',
                    OLD.deleted_at, OLD.deleted_by, OLD.deleted_ip, OLD.version,
                    JSON_OBJECT(
                        'first_name', OLD.first_name,
                        'last_name', OLD.last_name,
                        'phone', OLD.phone,
                        'address_line1', OLD.address_line1,
                        'address_line2', OLD.address_line2,
                        'city', OLD.city,
                        'state', OLD.state,
                        'postal_code', OLD.postal_code,
                        'country', OLD.country,
                        'profile_picture', OLD.profile_picture,
                        'preferences', OLD.preferences,
                        'gender', OLD.gender
                    ),
                    NULL
                );
            END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_profile_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_profile_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_profile_delete;");
    }
}
