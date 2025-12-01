<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Migrations;

use App\Infrastructure\Persistence\Core\Migrations\MigrationInterface;
use SafeMySQL;

/**
 * Migration_20250910221501_create_user_audit_trigger
 *
 * Creates audit triggers on 'user' table to log insert, update, and delete events
 * into the centralized global audit_log table, including created/updated/deleted at/by/ip info and version.
 */
class Migration_20250910221501_create_user_audit_trigger implements MigrationInterface
{
    public function getVersion(): string
    {
        return '20250910221501_create_user_audit_trigger';
    }

    public function getDescription(): string
    {
        return 'Creates audit triggers on user table for insert, update, delete events to global audit_log including comprehensive audit metadata.';
    }

    public function getDependencies(): array
    {
        return ['20250910221500_create_user_table'];
    }

    public function up(SafeMySQL $db): void
    {
        // Drop existing triggers if they exist
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_delete;");

        // INSERT audit trigger
        $db->query("
            CREATE TRIGGER trg_audit_user_insert
            AFTER INSERT ON user
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
                ) VALUES (
                    UUID(), 'user', NEW.id, 'created', NEW.created_at, NEW.created_by, NEW.created_ip, 1, NULL,
                    JSON_OBJECT(
                        'username', NEW.username,
                        'email', NEW.email,
                        'status', NEW.status
                    )
                );
            END
        ");

        // UPDATE audit trigger
        $db->query("
            CREATE TRIGGER trg_audit_user_update
            AFTER UPDATE ON user
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
                ) VALUES (
                    UUID(), 'user', NEW.id, 'updated', NEW.updated_at, NEW.updated_by, NEW.updated_ip, NEW.version,
                    JSON_OBJECT(
                        'username', OLD.username,
                        'email', OLD.email,
                        'status', OLD.status
                    ),
                    JSON_OBJECT(
                        'username', NEW.username,
                        'email', NEW.email,
                        'status', NEW.status
                    )
                );
            END
        ");

        // DELETE audit trigger
        $db->query("
            CREATE TRIGGER trg_audit_user_delete
            AFTER DELETE ON user
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    audit_id, entity_type, entity_id, action, changed_at, changed_by, changed_ip, version, old_value, new_value
                ) VALUES (
                    UUID(), 'user', OLD.id, 'deleted', OLD.deleted_at, OLD.deleted_by, OLD.deleted_ip, OLD.version,
                    JSON_OBJECT(
                        'username', OLD.username,
                        'email', OLD.email,
                        'status', OLD.status
                    ),
                    NULL
                );
            END
        ");
    }

    public function down(SafeMySQL $db): void
    {
        // Drop audit triggers on rollback
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_insert;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_update;");
        $db->query("DROP TRIGGER IF EXISTS trg_audit_user_delete;");
    }
}
