<?php
declare(strict_types=1);

namespace App\Domain\Session\Validation\Traits;

use App\Domain\Shared\Validation\Rules\NotEmptyRule;
use App\Domain\Shared\Validation\Rules\PatternRule;
use App\Domain\Shared\Validation\Rules\EnumInstanceRule;
use App\Domain\Shared\Validation\Rules\ExpirationTimeRule;
use App\Domain\Shared\Validation\Traits\ValidationGuard;
use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Session\Entities\SessionInterface;

trait SessionValidationGuard
{
    use ValidationGuard;

    // Validator must provide Session entity to trait via this method
    abstract protected function getSession(): SessionInterface;

    protected function getValidationRules(): array
    {
        return [
            'id' => new NotEmptyRule('Session ID must not be empty.'),
            'userId' => new NotEmptyRule('User ID must not be empty.'),
            'accessToken' => new PatternRule('/^[a-f0-9]{64}$/i', 'Access token must be 64 hex characters.'),
            'refreshToken' => new PatternRule('/^[a-zA-Z0-9\-_]{128}$/', 'Refresh token format is invalid.', true),
            'csrfToken' => new PatternRule('/^[a-zA-Z0-9\-_]{32,255}$/', 'CSRF token is invalid.', true),
            'createdInfo' => new NotEmptyRule('Created info must be provided.'),
            'updatedInfo' => new NotEmptyRule('Updated info must be provided.', true),
            'expiresAt' => new ExpirationTimeRule('ExpiresAt must be a valid non-expired time.'),
            'createdIp' => new NotEmptyRule('Created IP must be provided.'),
            'lastIpAddress' => new NotEmptyRule('Last IP address must be provided.', true),
            'sessionStatus' => new EnumInstanceRule(SessionStatus::class, 'Invalid session status.'),
        ];
    }

    protected function getValidationData(): array
    {
        $session = $this->getSession();

        return [
            'id' => (string) $session->getId(),
            'userId' => (string) $session->getUserId(),
            'accessToken' => $this->extractTokenString($session->getAccessToken()),
            'refreshToken' => $this->extractTokenString($session->getRefreshToken()),
            'csrfToken' => $this->extractTokenString($session->getCsrfToken()),
            'createdInfo' => $session->getCreatedInfo(),
            'updatedInfo' => $session->getUpdatedInfo(),
            'expiresAt' => $session->getExpiresAt(),
            'createdIp' => $session->getCreatedIp(),
            'lastIpAddress' => $session->getLastIpAddress(),
            'sessionStatus' => $session->getStatus(),
        ];
    }

    private function extractTokenString(mixed $token): ?string
    {
        if ($token === null) {
            return null;
        }
        if (is_string($token)) {
            return $token;
        }
        if (is_object($token) && method_exists($token, 'getToken')) {
            $value = $token->getToken();
            return is_string($value) ? $value : null;
        }
        if (is_array($token) && isset($token[0]) && is_string($token[0])) {
            return $token[0];
        }
        return null;
    }
}
