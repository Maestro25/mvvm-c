<?php
declare(strict_types=1);

namespace App\Domain\Session\Repositories;

interface SessionStorageInterface
{
    public function open(string $savePath, string $sessionName): bool;

    public function close(): bool;

    public function read(string $sessionId): string;

    /**
     * @param string $sessionId
     * @param string $data
     * @param string|null $userId
     * @return bool
     */
    public function write(string $sessionId, string $data, ?string $userId = null): bool;

    public function destroy(string $sessionId): bool;

    public function gc(int $maxLifetime): bool;
}
