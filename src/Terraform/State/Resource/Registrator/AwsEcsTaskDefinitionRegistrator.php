<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State\Resource\Registrator;

use MichalHepner\Tfimport\Terraform\State\Resource;
use MichalHepner\Tfimport\Terraform\State\ResourceInstance;
use MichalHepner\Tfimport\Terraform\State\ResourceMode;
use MichalHepner\Tfimport\Terraform\State\StateFile;

class AwsEcsTaskDefinitionRegistrator implements RegistratorInterface
{
    private const ARN_REGEX = '/^arn:aws:ecs:(?P<region>[a-z0-9\-]+):(?P<accountId>[0-9]+):task\-definition\/(?P<family>[a-zA-Z0-9\-\_]+)(:(?P<revision>[0-9]+)){0,1}$/';

    public function canRegister(
        StateFile $stateFile,
        string $resourceType,
        string $resourceName,
        mixed $resourceId
    ): bool {
        return $resourceType === 'aws_ecs_task_definition';
    }

    public function register(StateFile $stateFile, string $resourceType, string $resourceName, mixed $resourceId): void
    {
        $matches = [];
        if (!preg_match(self::ARN_REGEX, $resourceId, $matches)) {
            throw new \RuntimeException(sprintf(
                'You must provide an ARN of aws_ecs_task_definition as ID. \'%s\' was given',
                $resourceId
            ));
        }



        $stateFile->addResource(
            new Resource(
                ResourceMode::MANAGED,
                $resourceType,
                $resourceName,
                'provider["registry.terraform.io/hashicorp/aws"]',
                [
                    new ResourceInstance(0, [
                        'arn' => $resourceId,
                        'id' => $matches['family'],
                        'family' => $matches['family'],
                    ]),
                ]
            )
        );
    }
}
