<?php
declare(strict_types=1);

namespace App\Presentation\Observers\Interfaces;

/**
 * ObserverInterface defines a method to handle notifications from observables.
 */
interface ObserverInterface
{
    /**
     * Reacts to a notification from the observable subject.
     *
     * @param object $subject The observable subject (e.g., a ViewModel).
     * @param string|null $event Optional event name describing the update.
     * @param mixed|null $payload Additional contextual information.
     */
    public function update(object $subject, ?string $event = null, mixed $payload = null): void;
}
