<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use InvalidArgumentException;

abstract class ValueObjectException extends InvalidArgumentException
{
}
