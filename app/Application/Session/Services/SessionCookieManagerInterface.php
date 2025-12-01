<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

interface SessionCookieManagerInterface
{
    public function applyCookieParams(): void;

    public function renewSessionCookie(): void;

    public function clearSessionCookie(): void;

    public function getSessionCookieValue(): ?string;
}
