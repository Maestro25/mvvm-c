<?php
declare(strict_types=1);

namespace App\Domain\Blog\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

enum PostStatus: string
{
    use EnumHelpers;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
            self::DELETED => 'Deleted',
        };
    }
}
