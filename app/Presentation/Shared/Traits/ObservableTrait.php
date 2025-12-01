<?php
declare(strict_types=1);

namespace App\Presentation\Shared\Traits;

use App\Presentation\Observers\Interfaces\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * ObservableTrait provides prioritized observer management and notification support.
 */
trait ObservableTrait
{
    private array $observers = [];

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function attachObserver(ObserverInterface|callable $observer, int $priority = 0): void
    {
        $this->observers[$priority][] = $observer;
        krsort($this->observers);
    }

    public function detachObserver(ObserverInterface|callable $observer): bool
    {
        foreach ($this->observers as $priority => &$observersAtPriority) {
            foreach ($observersAtPriority as $key => $registeredObserver) {
                if ($registeredObserver === $observer) {
                    unset($observersAtPriority[$key]);
                    $observersAtPriority = array_values($observersAtPriority);
                    if (empty($observersAtPriority)) {
                        unset($this->observers[$priority]);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Notify all observers.
     * @param string|null $event Event name string (e.g. 'enterPage')
     * @param mixed|null $payload Event payload data (array or other)
     */
    public function notifyObservers(?string $event = null, mixed $payload = null): void
    {
        foreach ($this->observers as $priority => $observersAtPriority) {
            foreach ($observersAtPriority as $observer) {
                try {
                    if ($observer instanceof ObserverInterface) {
                        $observer->update($this, $event, $payload);
                    } elseif (is_callable($observer)) {
                        // Pass $event as string, $payload as array/mixed according to observer signature
                        call_user_func($observer, $event, is_array($payload) ? $payload : []);
                    } else {
                        $this->logError('Invalid observer attached; must implement ObserverInterface or be callable.');
                    }
                } catch (\Throwable $e) {
                    $this->logError('Observer notification error: ' . $e->getMessage(), $e);
                }
            }
        }
    }

    protected function logError(string $message, ?\Throwable $e = null): void
    {
        if ($this->logger !== null) {
            $this->logger->error($message, $e !== null ? ['exception' => $e] : []);
        }
    }
}
