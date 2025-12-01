<?php
declare(strict_types=1);

namespace App\Infrastructure\Hydrators;
/**
 * Interface for a Hydrator responsible for populating ViewModels from array data.
 */
interface HydratorInterface
{
    /**
     * Hydrate the given object with provided data.
     *
     * @param object $viewModel The ViewModel instance to hydrate.
     * @param array<string, mixed> $data Associative array of data to hydrate from.
     *
     * @return void
     *
     * @throws \RuntimeException for missing properties or type errors.
     */
    public function hydrate(object $viewModel, array $data): void;
}
