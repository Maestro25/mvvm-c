<?php
declare(strict_types=1);

namespace App\Presentation\ViewModels\Factories;

use App\Presentation\ViewModels\AuthViewModel;
use App\Presentation\ViewModels\LoginViewModel;
use App\Presentation\ViewModels\RegisterViewModel;
use App\Application\Services\Authentication\Interfaces\AuthServiceInterface;
use App\Application\Services\Session\Interfaces\SessionServiceInterface;
use App\Presentation\ViewModels\Factories\Interfaces\ViewModelFactoryInterface;
use App\Presentation\ViewModels\Interfaces\AuthViewModelInterface;
use App\Presentation\ViewModels\Interfaces\MainShellViewModelInterface;
use App\Presentation\ViewModels\Interfaces\RegisterViewModelInterface;
use App\Presentation\ViewModels\MainShellViewModel;
use Psr\Log\LoggerInterface;

final class ViewModelFactory
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly SessionServiceInterface $sessionService,
        private readonly LoggerInterface $logger
    ) {
    }

    


}




