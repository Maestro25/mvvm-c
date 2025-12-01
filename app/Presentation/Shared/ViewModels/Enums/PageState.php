<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels\Enums;

enum PageState: string
{
    case INITIAL = 'initial';          // Default starting state
    case LOADING = 'loading';          // Data or UI is loading
    case LOADED = 'loaded';            // Data successfully loaded
    case EMPTY = 'empty';              // No data to show
    case ERROR = 'error';              // Error occurred during loading
    case REFRESHING = 'refreshing';    // UI is refreshing data
    case SUBMITTING = 'submitting';    // User action submitting data
    case SUCCESS = 'success';          // Action succeeded
    case VALIDATING = 'validating';    // Input validation in progress
    case INVALID = 'invalid';          // Input validation failed
    case DISABLED = 'disabled';        // UI disabled state, e.g. for maintenance
    case OFFLINE = 'offline';          // No network connectivity
}
