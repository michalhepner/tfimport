<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State\Resource\Registrator;

use MichalHepner\Tfimport\Terraform\State\StateFile;

interface RegistratorInterface
{
    public function canRegister(StateFile $stateFile, string $resourceType, string $resourceName, mixed $resourceId): bool;
    public function register(StateFile $stateFile, string $resourceType, string $resourceName, mixed $resourceId): void;
}
