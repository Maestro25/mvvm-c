<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when an invalid profile picture is used in domain.
 */
final class InvalidProfilePictureException extends DomainException
{
    public static function emptyPath(): self
    {
        return new self('Profile picture path cannot be empty.');
    }

    public static function invalidFormat(string $extension): self
    {
        return new self("Profile picture format '{$extension}' is not allowed. Allowed formats: jpg, jpeg, png, gif, bmp, webp.");
    }
}
