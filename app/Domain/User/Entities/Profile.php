<?php
declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\Shared\Entities\Entity;
use App\Domain\User\ValueObjects\Name;
use App\Domain\User\ValueObjects\Phone;
use App\Domain\User\ValueObjects\Address;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\ProfilePicture;
use App\Domain\User\ValueObjects\Preferences;
use App\Domain\User\Exceptions\InvalidProfileException;
use App\Domain\Shared\ValueObjects\ProfileId;
use App\Domain\User\Enums\Gender;

final class Profile extends Entity
{
    public function __construct(
        private ProfileId $profileId,
        private Name $name,
        private Phone $phone,
        private Address $address,
        private Email $email,
        private ?ProfilePicture $profilePicture,
        private Preferences $preferences,
        private Gender $gender = Gender::UNKNOWN
    ) {
        parent::__construct($profileId);
    }

    public function getId(): ProfileId
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function setName(Name $name): void
    {
        $this->name = $name;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    public function setPhone(Phone $phone): void
    {
        $this->phone = $phone;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function getProfilePicture(): ?ProfilePicture
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?ProfilePicture $profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }

    public function getPreferences(): Preferences
    {
        return $this->preferences;
    }

    public function setPreferences(Preferences $preferences): void
    {
        $this->preferences = $preferences;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * Business logic example:
     * Validate profile completeness
     */
    public function validateProfileCompleteness(): bool
    {
        if (
            empty((string)$this->email) ||
            empty((string)$this->name) ||
            empty((string)$this->phone)
        ) {
            throw new InvalidProfileException('Profile incomplete: required fields missing.');
        }

        return true;
    }
}
