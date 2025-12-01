<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels;

use App\Presentation\Observers\Interfaces\ObserverInterface;
use JsonSerializable;

interface ViewModelInterface extends JsonSerializable
{
    /**
     * Validates the ViewModel state.
     *
     * @return bool True if valid, false otherwise.
     */
    public function validate(): bool;

    /**
     * Returns an array of validation errors keyed by property.
     *
     * @return array<string, string[]>
     */
    public function getValidationErrors(): array;

    /**
     * Clears all accumulated validation errors.
     */
    public function clearValidationErrors(): void;

    /**
     * Converts the ViewModel's public data into an array for views or API response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Attach an observer for event notifications.
     *
     * @param callable $observer
     */
    /**
     * Attach an observer for event notifications.
     *
     * @param ObserverInterface|callable $observer
     * @param int $priority Optional priority for notification order, default 0
     */
    public function attachObserver(ObserverInterface|callable $observer, int $priority = 0): void;


    /**
     * Detach an observer.
     *
     * @param callable $observer
     */
    public function detachObserver(ObserverInterface|callable $observer): bool;

    /**
     * Notify all observers with an event name and optional data.
     *
     * @param string $event
     * @param mixed|null $payload
     */
    public function notifyObservers(string $event, $payload = null): void;

    public function getFlashMessages(): array;

    public function addFlashMessage(string $type, string $message): void;

    public function getNotifications(): array;
    
    public function addNotification(array $notification): void;
}
