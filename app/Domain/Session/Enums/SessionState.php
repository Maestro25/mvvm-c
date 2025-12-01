<?php
declare(strict_types=1);

namespace App\Domain\Session\Enums;

enum SessionState: string
{
    case INITIALIZED = 'initialized';     // Session initialized but not started
    case STARTED = 'started';             // Session successfully started
    case REGENERATED = 'regenerated';     // Session ID regenerated for security
    case DESTROYED = 'destroyed';         // Session destroyed/ended
    case EXPIRED = 'expired';             // Session expired due to inactivity
    case FAILED = 'failed';               // Session failed to start or reload
    case PAUSED = 'paused';               // Session temporarily paused (optional for advanced control)
}
