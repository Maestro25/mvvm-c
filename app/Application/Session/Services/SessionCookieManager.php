<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use Psr\Log\LoggerInterface;

final class SessionCookieManager implements SessionCookieManagerInterface
{
    public function __construct(
        private readonly string $cookieName,
        private readonly int $cookieLifetime,
        private readonly string $cookiePath,
        private readonly ?string $cookieDomain,
        private readonly bool $cookieSecure,
        private readonly bool $cookieHttpOnly,
        private readonly string $cookieSameSite, // Use 'Lax' or 'Strict' for best CSRF protection, 'None' with Secure if cross-site needed
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Configure session cookie parameters before session_start().
     * Best practice: use SameSite=Lax or Strict, Secure=true if HTTPS,
     * HttpOnly=true to prevent JS access.
     */
    public function applyCookieParams(): void
    {
        try {
            session_set_cookie_params([
                'lifetime' => $this->cookieLifetime,
                'path' => $this->cookiePath,
                'domain' => $this->cookieDomain,
                'secure' => $this->cookieSecure,      // True in production HTTPS; false ONLY in trusted local dev
                'httponly' => $this->cookieHttpOnly,  // Prevent JavaScript access to mitigate XSS
                'samesite' => $this->cookieSameSite,  // Strict or Lax strongly recommended
            ]);
            session_name($this->cookieName);

            $this->logger->info('Session cookie parameters applied', [
                'name' => $this->cookieName,
                'lifetime' => $this->cookieLifetime,
                'path' => $this->cookiePath,
                'domain' => $this->cookieDomain,
                'secure' => $this->cookieSecure,
                'httponly' => $this->cookieHttpOnly,
                'samesite' => $this->cookieSameSite,
                'headers_sent' => headers_sent() ? 'yes' : 'no',
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to apply session cookie parameters', [
                'exception' => $e,
                'name' => $this->cookieName,
                'headers_sent' => headers_sent() ? 'yes' : 'no',
            ]);
            throw $e;
        }
    }

    /**
     * Renew the session cookie expiration on each request.
     * Only renew if the session is active.
     * This keeps the cookie fresh and prevents premature logout.
     */
    public function renewSessionCookie(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->logger->warning('Attempted to renew session cookie with inactive session', [
                'cookie_name' => $this->cookieName,
                'session_status' => session_status(),
            ]);
            return;
        }

        try {
            $cookieOptions = [
                'expires' => time() + $this->cookieLifetime,
                'path' => $this->cookiePath,
                'domain' => $this->cookieDomain,
                'secure' => $this->cookieSecure,
                'httponly' => $this->cookieHttpOnly,
                'samesite' => $this->cookieSameSite,
            ];

            $success = setcookie($this->cookieName, session_id(), $cookieOptions);
            if ($success) {
                $this->logger->info('Session cookie renewed successfully', [
                    'cookie_name' => $this->cookieName,
                    'new_expiry_unix' => $cookieOptions['expires'],
                    'new_expiry_datetime' => date('c', $cookieOptions['expires']),
                ]);
            } else {
                $this->logger->warning('Failed to renew session cookie', [
                    'cookie_name' => $this->cookieName,
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Exception during session cookie renewal', [
                'exception' => $e,
                'cookie_name' => $this->cookieName,
            ]);
            throw $e;
        }
    }

    /**
     * Clear the session cookie explicitly on logout or session destroy.
     * Sets expiry in the past with consistent cookie options.
     */
    public function clearSessionCookie(): void
    {
        try {
            $cookieOptions = [
                'expires' => time() - 3600,  // Past time to delete cookie
                'path' => $this->cookiePath,
                'domain' => $this->cookieDomain,
                'secure' => $this->cookieSecure,
                'httponly' => $this->cookieHttpOnly,
                'samesite' => $this->cookieSameSite,
            ];

            $success = setcookie($this->cookieName, '', $cookieOptions);
            if ($success) {
                $this->logger->info('Session cookie cleared successfully', [
                    'cookie_name' => $this->cookieName,
                ]);
            } else {
                $this->logger->warning('Failed to clear session cookie', [
                    'cookie_name' => $this->cookieName,
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Exception during session cookie clear', [
                'exception' => $e,
                'cookie_name' => $this->cookieName,
            ]);
            throw $e;
        }
    }

    /**
     * Get current session cookie raw value from $_COOKIE global.
     */
    public function getSessionCookieValue(): ?string
    {
        $cookieValue = $_COOKIE[$this->cookieName] ?? null;
        $this->logger->debug('Retrieved session cookie value', [
            'cookie_name' => $this->cookieName,
            'cookie_value_present' => $cookieValue !== null,
        ]);
        return $cookieValue;
    }
}
