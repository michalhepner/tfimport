<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State\Resource\Registrator;

use MichalHepner\Tfimport\Terraform\State\Resource;
use MichalHepner\Tfimport\Terraform\State\ResourceInstance;
use MichalHepner\Tfimport\Terraform\State\ResourceMode;
use MichalHepner\Tfimport\Terraform\State\StateFile;

class DefaultRegistrator implements RegistratorInterface
{
    public function __construct(protected array $resourceTypeProviders) {}

    public function canRegister(
        StateFile $stateFile,
        string $resourceType,
        string $resourceName,
        mixed $resourceId
    ): bool {
        return array_key_exists($resourceType, $this->resourceTypeProviders);
    }

    public function register(StateFile $stateFile, string $resourceType, string $resourceName, mixed $resourceId): void
    {
        $stateFile->addResource(
            new Resource(
                ResourceMode::MANAGED,
                $resourceType,
                $resourceName,
                'provider["' . $this->resourceTypeProviders[$resourceType] . '"]',
                [
                    new ResourceInstance(0, [
                        'id' => $resourceId,
                    ]),
                ]
            )
        );
    }
}
