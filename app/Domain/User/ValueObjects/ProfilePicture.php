<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidProfilePictureException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class ProfilePicture extends ValueObject
{
    private const ALLOWED_FILE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    private readonly string $path;

    public function __construct(string $path)
    {
        $path = trim($path);

        if (empty($path)) {
            throw InvalidProfilePictureException::emptyPath();
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_FILE_EXTENSIONS, true)) {
            throw InvalidProfilePictureException::invalidFormat($extension);
        }

        $this->path = $path;
    }

    protected function getAtomicValues(): array
    {
        return [$this->path];
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
