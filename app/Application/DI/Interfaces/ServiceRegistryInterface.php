<?php
declare(strict_types=1);

namespace App\Application\DI\Interfaces;

use App\Application\DI\Interfaces\DIContainerInterface;

interface ServiceRegistryInterface
{
    public function registerServices(DIContainerInterface $container): void;
}

