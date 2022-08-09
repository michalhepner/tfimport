<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Console\Command;

use MichalHepner\Tfimport\Terraform\Command\InitCommand;
use MichalHepner\Tfimport\Terraform\Command\RefreshCommand;
use MichalHepner\Tfimport\Terraform\Command\ShowCommand;
use MichalHepner\Tfimport\Terraform\Provider\SchemaProvider;
use MichalHepner\Tfimport\Terraform\State\Resource;
use MichalHepner\Tfimport\Terraform\State\ResourceInstance;
use MichalHepner\Tfimport\Terraform\State\ResourceMode;
use MichalHepner\Tfimport\Terraform\State\StateFile;
use MichalHepner\Tfimport\Terraform\State\StateFileFilter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RunCommand extends Command
{
    protected LoggerInterface $logger;

    protected function configure(): void
    {
        $this->setName('run');
        $this->setDescription('Obtain resource terraform config');
        $this->addArgument(
            'resource-spec',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'A list of resource specs in the following format TERRAFORM_RESOURCE_TYPE:RESOURCE_NAME:RESOURCE_ID',
        );
        $this->addOption('no-cache', 'c', InputOption::VALUE_NONE);
        $this->addUsage('tfimport.phar run aws_instance:some_name:i-0123456 aws_iam_role:admin_role:tf-admin');
        $this->addUsage('bin/console run aws_secretsmanager_secret:some_secret:arn:aws:secretsmanager:eu-west-1:1234567890:secret:some/path/to/secret');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger = new ConsoleLogger($output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

        $fs = new Filesystem();

        if ($input->getOption('no-cache') && $fs->exists(getcwd() . '/.terraform')) {
            $this->logger->info('Removing old terraform files..');
            $fs->remove(getcwd() . '/.terraform');
        }

        if (!$fs->exists(getcwd() . '/terraform.tfstate')) {
            $stateFile = new StateFile();
            $this->logger->info('Creating state file..');
            $fs->dumpFile(getcwd() . '/terraform.tfstate', $stateFile->toJson());
        } else {
            $stateFile = StateFile::load(getcwd() . '/terraform.tfstate');
        }

        $this->logger->info('Initializing terraform..');
        (new InitCommand(logger: $this->logger))->run();

        $this->logger->info('Querying provider info..');
        $providerSchemas = (new SchemaProvider(logger: $this->logger))->fromModulePath(getcwd());

        $resourceTypeSchemas = $this->getResourceTypesSchemas($providerSchemas);

        $this->logger->info('Updating state file to match current config..');
        $this->removeMissingResources($stateFile);

        $resourcesInScope = [];
        foreach ($input->getArgument('resource-spec') as $resourceSpec) {
            $matches = [];
            if (!preg_match('/^(?P<resourceType>[a-z0-9_]+):(?P<resourceName>[a-z]+[a-z0-9_\-]*):(?P<resourceId>.+)$/', $resourceSpec, $matches)) {
                $this->logger->error('Invalid resource spec provided. Each item must follow resource_type:resource_name:resource_id format');

                return 1;
            }

            $resourceType = $matches['resourceType'];
            $resourceName = $matches['resourceName'];
            $resourceId = $matches['resourceId'];

            if (!array_key_exists($resourceType, $resourceTypeSchemas)) {
                $this->logger->error(sprintf(
                    'Unknown resource type %s. Please make sure that all providers are properly set.',
                    $resourceType
                ));

                return 1;
            }

            $this->logger->info(sprintf(
                'Registering resource %s of type %s with id %s',
                $resourceName,
                $resourceType,
                $resourceId
            ));

            !array_key_exists($resourceType, $resourcesInScope) && $resourcesInScope[$resourceType] = [];
            $resourcesInScope[$resourceType][] = $resourceName;

            $stateFile->addResource(
                new Resource(
                    ResourceMode::MANAGED,
                    $resourceType,
                    $resourceName,
                    'provider["' . $resourceTypeSchemas[$resourceType] . '"]',
                    [
                        new ResourceInstance(0, [
                            'id' => $resourceId,
                        ]),
                    ]
                )
            );
        }


        $this->logger->info('Updating state file..');
        $fs->dumpFile(getcwd() . '/terraform.tfstate', $stateFile->toJson());
        $fs->copy(getcwd() . '/terraform.tfstate', getcwd() . '/terraform.tfstate.backup', true);

        $this->logger->info('Fetching information about the resources..');
        (new RefreshCommand(logger: $this->logger))->run();

        $fs->copy(getcwd() . '/terraform.tfstate', getcwd() . '/terraform.tfstate.backup', true);

        $stateFile = StateFile::load(getcwd() . '/terraform.tfstate');

        $this->logger->info('Filtering extraneous data..');

        $this->removeOutOfScopeResources($stateFile, $resourcesInScope);
        $stateFile = (new StateFileFilter($providerSchemas))->filter($stateFile);

        $fs->dumpFile(getcwd() . '/terraform.tfstate', $stateFile->toJson());

        $showCmd = (new ShowCommand(logger: $this->logger));
        $showCmd->run();

        $fs->copy(getcwd() . '/terraform.tfstate.backup', getcwd() . '/terraform.tfstate', true);

        $output->write($showCmd->getOutput());

        return 0;
    }

    protected function getResourceTypesSchemas(array $providerSchemas): array
    {
        $ret = [];
        foreach ($providerSchemas as $providerName => $providerSchema) {
            if ($providerSchema->getResourceSchemas() !== null) {
                foreach (array_keys($providerSchema->getResourceSchemas()) as $resourceName) {
                    $ret[$resourceName] = $providerName;
                }
            }
        }

        return $ret;
    }

    protected function removeMissingResources(StateFile $stateFile): void
    {
        $foundResources = [
            ResourceMode::MANAGED->value => [],
            ResourceMode::DATA->value => [],
        ];

        /** @var SplFileInfo $tfFile */
        foreach ((new Finder())->files()->name('*.tf')->in(getcwd()) as $tfFile) {
            $tfFileContents = file_get_contents($tfFile->getPathname());
            $fileLines = explode(PHP_EOL, $tfFileContents);
            foreach ($fileLines as $line) {
                $matches = [];
                if (preg_match('/^(?P<mode>resource|data) "(?P<type>[a-z\_]+)" "(?P<name>[a-z0-9A-Z_\-]+)"[ ]+{/', $line, $matches)) {
                    $mode = match($matches['mode']) {
                        'resource' => ResourceMode::MANAGED->value,
                        'data' => ResourceMode::DATA->value,
                    };
                    $type = $matches['type'];
                    $name = $matches['name'];

                    !array_key_exists($type, $foundResources[$mode]) && $foundResources[$mode][$type] = [];
                    $foundResources[$mode][$type][] = $name;
                }
            }
        }

        /** @var Resource $resource */
        foreach ($stateFile->getResources() as $resource) {
            $mode = $resource->getMode()->value;
            $type = $resource->getType();
            $name = $resource->getName();
            if (!isset($foundResources[$mode][$type]) || !in_array($name, $foundResources[$mode][$type])) {
                $this->logger->info(sprintf(
                    'Removing resource %s.%s from state file as its configuration is not present.',
                    $resource->getType(),
                    $resource->getName(),
                ));
                $stateFile->removeResource($resource->getMode(), $resource->getType(), $resource->getName());
            }
        }
    }

    protected function removeOutOfScopeResources(StateFile $stateFile, array $resourcesInScope): void
    {
        /** @var Resource $resource */
        foreach ($stateFile->getResources() as $resource) {
            $shouldDelete = false;
            if ($resource->getMode() === ResourceMode::DATA) {
                $shouldDelete = true;
            } elseif (!isset($resourcesInScope[$resource->getType()]) || !in_array($resource->getName(), $resourcesInScope[$resource->getType()])) {
                $shouldDelete = true;
            }

            if ($shouldDelete) {
                $stateFile->removeResource($resource->getMode(), $resource->getType(), $resource->getName());
            }
        }
    }
}
