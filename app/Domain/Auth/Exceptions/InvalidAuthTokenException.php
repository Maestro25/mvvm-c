<?php
declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

use App\Domain\Shared\Exceptions\ValueObjectException;

final class InvalidAuthTokenException extends ValueObjectException
{}
