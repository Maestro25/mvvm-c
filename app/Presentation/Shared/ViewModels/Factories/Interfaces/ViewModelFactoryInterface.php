<?php
declare(strict_types=1);

namespace App\Presentation\ViewModels\Factories\Interfaces;

use App\Presentation\ViewModels\AuthViewModel;
use App\Presentation\ViewModels\MainViewModel;
use App\Presentation\ViewModels\ViewModel;
use Psr\Log\LoggerInterface;
use App\Application\Security\Interfaces\InputSanitizerInterface;
use App\Presentation\ViewModels\Interfaces\AuthViewModelInterface;
use App\Presentation\ViewModels\Interfaces\MainShellViewModelInterface;

/**
 * Interface ViewModelFactoryInterface
 * Defines methods for ViewModel creation
 */
interface ViewModelFactoryInterface
{

    public function createMainViewModel(array $data = []): MainShellViewModelInterface;

    public function createAuthViewModel(): AuthViewModelInterface;

    public function createRegisterViewModel(): RegisterViewModelInterface;

    // Add other ViewModel creation methods as needed
}
