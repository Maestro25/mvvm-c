<?php
declare(strict_types=1);

namespace App\Domain\Auth\RBAC\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

/**
 * Enum representing permission names with fine-grained user management and system permissions.
 */
enum PermissionName: string
{
    use EnumHelpers;

    // User Management
    case MANAGE_USERS = 'manage_users';
    case CREATE_USERS = 'create_users';
    case EDIT_USERS = 'edit_users';
    case DELETE_USERS = 'delete_users';
    case VIEW_USERS = 'view_users';

    // Content Management
    case EDIT_ARTICLES = 'edit_articles';
    case CREATE_ARTICLES = 'create_articles';
    case DELETE_ARTICLES = 'delete_articles';
    case PUBLISH_ARTICLES = 'publish_articles';
    case VIEW_ARTICLES = 'view_articles';

    // Reporting
    case VIEW_REPORTS = 'view_reports';
    case EXPORT_REPORTS = 'export_reports';
    case GENERATE_REPORTS = 'generate_reports';

    // System Administration
    case MANAGE_SETTINGS = 'manage_settings';
    case VIEW_AUDIT_LOGS = 'view_audit_logs';
    case MANAGE_ROLES = 'manage_roles';
    case MANAGE_PERMISSIONS = 'manage_permissions';

    // Security and Sessions
    case MANAGE_SESSIONS = 'manage_sessions';
    case RESET_PASSWORDS = 'reset_passwords';
    case ENABLE_MFA = 'enable_mfa';

    // Miscellaneous
    case APPROVE_CONTENT = 'approve_content';
    case COMMENT_ON_CONTENT = 'comment_on_content';
    case VIEW_DASHBOARD = 'view_dashboard';

    public function label(): string
    {
        return match ($this) {
            self::MANAGE_USERS => 'Manage Users',
            self::CREATE_USERS => 'Create Users',
            self::EDIT_USERS => 'Edit Users',
            self::DELETE_USERS => 'Delete Users',
            self::VIEW_USERS => 'View Users',

            self::EDIT_ARTICLES => 'Edit Articles',
            self::CREATE_ARTICLES => 'Create Articles',
            self::DELETE_ARTICLES => 'Delete Articles',
            self::PUBLISH_ARTICLES => 'Publish Articles',
            self::VIEW_ARTICLES => 'View Articles',

            self::VIEW_REPORTS => 'View Reports',
            self::EXPORT_REPORTS => 'Export Reports',
            self::GENERATE_REPORTS => 'Generate Reports',

            self::MANAGE_SETTINGS => 'Manage Settings',
            self::VIEW_AUDIT_LOGS => 'View Audit Logs',
            self::MANAGE_ROLES => 'Manage Roles',
            self::MANAGE_PERMISSIONS => 'Manage Permissions',

            self::MANAGE_SESSIONS => 'Manage Sessions',
            self::RESET_PASSWORDS => 'Reset Passwords',
            self::ENABLE_MFA => 'Enable MFA',

            self::APPROVE_CONTENT => 'Approve Content',
            self::COMMENT_ON_CONTENT => 'Comment on Content',
            self::VIEW_DASHBOARD => 'View Dashboard',
        };
    }
}
