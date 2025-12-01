<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Session\Repositories;

use App\Domain\Session\Repositories\FileSessionStorageInterface;

final class FileSessionStorage implements FileSessionStorageInterface
{
    private string $savePath = '';

    public function open(string $savePath, string $sessionName): bool
    {
        // Set save path for session files if needed
        if ($savePath !== '') {
            $this->savePath = $savePath;
        }
        // No session_name or session_id manipulation here
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sessionId): string
    {
        // Build session file path according to PHP session.save_path and session_id
        if ($this->savePath === '') {
            $this->savePath = session_save_path() ?: sys_get_temp_dir();
        }
        $file = $this->savePath . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
        if (!is_file($file)) {
            return '';
        }
        $data = file_get_contents($file);
        return $data === false ? '' : $data;
    }

    public function write(string $sessionId, string $data, ?string $userId = null): bool
    {
        if ($this->savePath === '') {
            $this->savePath = session_save_path() ?: sys_get_temp_dir();
        }
        $file = $this->savePath . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
        $result = file_put_contents($file, $data);
        return $result !== false;
    }

    public function destroy(string $sessionId): bool
    {
        if ($this->savePath === '') {
            $this->savePath = session_save_path() ?: sys_get_temp_dir();
        }
        $file = $this->savePath . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
        if (is_file($file)) {
            return unlink($file);
        }
        return true;
    }

    public function gc(int $maxLifetime): bool
    {
        if ($this->savePath === '') {
            $this->savePath = session_save_path() ?: sys_get_temp_dir();
        }
        foreach (glob($this->savePath . DIRECTORY_SEPARATOR . 'sess_*') as $file) {
            if (filemtime($file) + $maxLifetime < time() && is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
}
