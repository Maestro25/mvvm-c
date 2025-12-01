<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use App\Domain\Session\Repositories\DbSessionStorageInterface;
use App\Domain\Session\Repositories\FileSessionStorageInterface;
use \SessionHandlerInterface;

final class CompositeSessionHandler implements SessionHandlerInterface
{
    public function __construct(
        private DbSessionStorageInterface $dbStorage,
        private FileSessionStorageInterface $fileStorage
    ) {
    }

    public function open(string $savePath, string $sessionName): bool
    {
        try {
            return $this->dbStorage->open($savePath, $sessionName);
        } catch (\Throwable $e) {
            return $this->fileStorage->open($savePath, $sessionName);
        }
    }

    public function close(): bool
    {
        try {
            return $this->dbStorage->close();
        } catch (\Throwable $e) {
            return $this->fileStorage->close();
        }
    }

    public function read(string $sessionId): string
    {
        try {
            $data = $this->dbStorage->read($sessionId);
            if ($data !== '') {
                return $data;
            }
        } catch (\Throwable $e) {
            // Log the exception as needed
        }
        return $this->fileStorage->read($sessionId);
    }

    public function write(string $sessionId, string $data): bool
    {
        try {
            return $this->dbStorage->write($sessionId, $data);
        } catch (\Throwable $e) {
            // Log the exception as needed
            return $this->fileStorage->write($sessionId, $data);
        }
    }

    public function destroy(string $sessionId): bool
    {
        try {
            $result = $this->dbStorage->destroy($sessionId);
            if ($result) {
                return true;
            }
        } catch (\Throwable $e) {
            // Log exception
        }
        return $this->fileStorage->destroy($sessionId);
    }

    public function gc(int $maxLifetime): int|false
    {
        try {
            $result = $this->dbStorage->gc($maxLifetime);
            if ($result === false) {
                return $this->fileStorage->gc($maxLifetime);
            }
            return is_int($result) ? $result : ($result ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->fileStorage->gc($maxLifetime);
        }
    }

}
