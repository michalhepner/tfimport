<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State\Resource\Registrator;

use MichalHepner\Tfimport\Terraform\State\StateFile;
use RuntimeException;

class Registrator implements RegistratorInterface
{
    protected array $registrators = [];

    public function addRegistrator(RegistratorInterface $registrator, int $priority = 0): void
    {
        $this->registrators[] = [$registrator, $priority];

        usort($this->registrators, function (array $a, array $b) {
            return $b[1] - $a[1];
        });
    }

    public function canRegister(
        StateFile $stateFile,
        string $resourceType,
        string $resourceName,
        mixed $resourceId
    ): bool {
        foreach ($this->registrators as $registratorItem) {
            list ($registrator, ) = $registratorItem;

            if ($registrator->canRegister($stateFile, $resourceType, $resourceName, $resourceId)) {
                return true;
            }
        }

        return false;
    }

    public function register(StateFile $stateFile, string $resourceType, string $resourceName, mixed $resourceId): void
    {
        foreach ($this->registrators as $registratorItem) {
            /** @var RegistratorInterface $registrator */
            list ($registrator, ) = $registratorItem;
            if ($registrator->canRegister($stateFile, $resourceType, $resourceName, $resourceId)) {
                $registrator->register($stateFile, $resourceType, $resourceName, $resourceId);

                return;
            }
        }

        throw new RuntimeException(sprintf(
            'Resource of type %s with name %s (id=%s) could not be registered. No registrator was capable of completing the task.',
            $resourceType,
            $resourceName,
            $resourceId,
        ));
    }
}
