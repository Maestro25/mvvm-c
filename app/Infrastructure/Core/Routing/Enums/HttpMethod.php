<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

enum HttpMethod: string
{
    use EnumHelpers;

    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';

    public function label(): string
    {
        return $this->value;
    }
}
