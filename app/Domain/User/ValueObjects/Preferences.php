<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidPreferencesException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class Preferences extends ValueObject
{
    /**
     * @var array<string, mixed>
     */
    private readonly array $preferences;

    public function __construct(array $preferences)
    {
        if (!$this->isValidPreferences($preferences)) {
            throw InvalidPreferencesException::invalidFormat();
        }

        // Defensive copy to maintain immutability
        $this->preferences = $this->deepCopyArray($preferences);
    }

    private function isValidPreferences(array $preferences): bool
    {
        // Implement actual validation logic for preferences schema
        return true;
    }

    private function deepCopyArray(array $arr): array
    {
        // Deep copy to prevent external mutation
        return unserialize(serialize($arr));
    }

    protected function getAtomicValues(): array
    {
        return [$this->preferences];
    }

    public function getPreferences(): array
    {
        // Return deep copy
        return $this->deepCopyArray($this->preferences);
    }

    public function __toString(): string
    {
        return json_encode($this->preferences, JSON_THROW_ON_ERROR);
    }
}
