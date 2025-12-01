<?php
declare(strict_types=1);

namespace App\Domain\User\Factories;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\User\DTOs\UserCreationData;
use App\Domain\Shared\Factories\EntityFactoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;

interface UserFactoryInterface extends EntityFactoryInterface
{
    

}
