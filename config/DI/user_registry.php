<?php
declare(strict_types=1);


use App\Domain\User\Factories\UserFactory;
use App\Domain\User\Factories\UserFactoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Core\DI\DIContainer;

use App\Common\Enums\ServiceLifetime;

use App\Infrastructure\Persistence\User\Mappers\UserDbMapper;
use App\Infrastructure\Persistence\User\Mappers\UserDbMapperInterface;
use App\Infrastructure\Persistence\User\Repositories\UserRepository;



function registerUser(DIContainer $container): void
{

    $container->bind(UserDbMapperInterface::class, UserDbMapper::class, ServiceLifetime::SINGLETON);
    $container->bind(UserFactoryInterface::class, UserFactory::class, ServiceLifetime::SINGLETON);
    // Bind SessionRepository
    $container->bind(UserRepositoryInterface::class, function (DIContainer $c) {
        return new UserRepository(
            $c->get(SafeMySQL::class),
            $c->get(UserDbMapperInterface::class)
        );
    }, ServiceLifetime::SINGLETON);
}
;
