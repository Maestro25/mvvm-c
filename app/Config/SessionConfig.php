<?php
declare(strict_types=1);

namespace App\Config;

/**
 * Session configuration settings used for secure session management in PHP 8.1+.
 */
final class SessionConfig
{
    public const COOKIE_LIFETIME = 0;
    public const COOKIE_PATH = '/';
    public const COOKIE_DOMAIN = '';
    public const COOKIE_SECURE = true;
    public const COOKIE_HTTPONLY = true;
    public const COOKIE_SAMESITE = 'Strict';
    public const USE_STRICT_MODE = true;
    public const SESSION_TIMEOUT = 1800; // 30 minutes
    public const ABSOLUTE_TIMEOUT = 28800; // 8 hours
    public const SESSION_LAST_REGENERATION_KEY = 'last_regeneration';
    public const SESSION_REGENERATION_INTERVAL = 1800; // 30 minutes

    /**
     * Absolute path where session files are stored.
     * Must be writable by PHP process.
     * Can be overridden by environment variable SESSION_SAVE_PATH
     */
    public static string $SESSION_SAVE_PATH;

    public static function apply(): void
    {
        self::$SESSION_SAVE_PATH = getenv('SESSION_SAVE_PATH') ?: __DIR__ . '/../../../runtime/sessions';

        if (!is_dir(self::$SESSION_SAVE_PATH)) {
            if (!mkdir(self::$SESSION_SAVE_PATH, 0700, true) && !is_dir(self::$SESSION_SAVE_PATH)) {
                throw new \RuntimeException('Failed to create session save path: ' . self::$SESSION_SAVE_PATH);
            }
        }
        if (!is_writable(self::$SESSION_SAVE_PATH)) {
            throw new \RuntimeException('Session save path is not writable: ' . self::$SESSION_SAVE_PATH);
        }

        session_save_path(self::$SESSION_SAVE_PATH);

        ini_set('session.use_strict_mode', self::USE_STRICT_MODE ? '1' : '0');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', (string) self::SESSION_TIMEOUT);

        session_set_cookie_params([
            'lifetime' => self::COOKIE_LIFETIME,
            'path' => self::COOKIE_PATH,
            'domain' => self::COOKIE_DOMAIN,
            'secure' => self::COOKIE_SECURE,
            'httponly' => self::COOKIE_HTTPONLY,
            'samesite' => self::COOKIE_SAMESITE,
        ]);
    }
}
