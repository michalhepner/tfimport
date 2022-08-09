<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State;

use MichalHepner\Tfimport\Terraform\Command\VersionCommand;
use MichalHepner\Tfimport\Uuid;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class StateFile
{
    protected int $version;
    protected string $terraformVersion;
    protected int $serial;
    protected string $lineage;
    protected mixed $outputs;

    /**
     * @var Resource[]
     */
    protected array $resources;

    public function __construct(
        ?int $version = null,
        ?string $terraformVersion = null,
        ?int $serial = null,
        ?string $lineage = null,
        mixed $outputs = null,
        ?array $resources = null,
    ) {
        $this->version = $version ?? 4;
        if (empty($terraformVersion)) {
            $versionCommand = new VersionCommand();
            $versionCommand->run();
            $terraformVersion = (json_decode($versionCommand->getOutput(), true))['terraform_version'];
        }
        $this->terraformVersion = $terraformVersion;
        $this->serial = $serial ?? 1;
        $this->lineage = empty($lineage) ? (new Uuid())->__toString() : $lineage;
        $this->outputs = $outputs ?? (object) [];
        $this->resources = $resources ?? [];
    }

    public static function load(string $path): self
    {
        $fs = new Filesystem();
        if (!$fs->exists($path)) {
            throw new RuntimeException(sprintf('Failed to load state file from %s. File does not exist', $path));
        }

        $rawContents = file_get_contents($path);
        $data = json_decode($rawContents);

        if (!is_object($data)) {
            throw new RuntimeException(sprintf('Failed to decode state file json from %s', $path));
        }

        if (!property_exists($data, 'version') || $data->version !== 4) {
            throw new RuntimeException(sprintf(
                'Failed to decode state file from %s. Only version 4 of the state file is accepted',
                $path
            ));
        }

        $self = new self();
        $self->version = $data->version;
        $self->terraformVersion = $data->terraform_version;
        $self->serial = $data->serial;
        $self->lineage = $data->lineage;
        $self->outputs = $data->outputs;
        $self->resources = array_map(
            fn ($resourceData) => Resource::fromArray((array) $resourceData),
            $data->resources,
        );

        return $self;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'terraform_version' => $this->terraformVersion,
            'serial' => $this->serial,
            'lineage' => $this->lineage,
            'outputs' => $this->outputs,
            'resources' => array_map(fn (Resource $resource) => $resource->toArray(), $this->resources),
        ];
    }

    public function addResource(Resource $resource): void
    {
        /** @var Resource $existingResource */
        foreach ($this->resources as $existingResource) {
            if ($existingResource->getMode() === $resource->getMode()
                && $existingResource->getType() === $resource->getType()
                && $existingResource->getName() === $resource->getName()
            ) {
                throw new \LogicException(sprintf(
                    'Cannot add resource %s.%s, as it\'s already present in the state file.',
                    $resource->getType(),
                    $resource->getName(),
                ));
            }
        }

        $this->resources[] = $resource;
    }

    public function hasResource(ResourceMode $mode, string $type, string $name): bool
    {
        /** @var Resource $existingResource */
        foreach ($this->resources as $k => $resource) {
            if ($resource->getMode() === $mode
                && $resource->getType() === $type
                && $resource->getName() === $name
            ) {
                return true;
            }
        }

        return false;
    }

    public function removeResource(ResourceMode $mode, string $type, string $name): void
    {
        /** @var Resource $existingResource */
        foreach ($this->resources as $k => $resource) {
            if ($resource->getMode() === $mode
                && $resource->getType() === $type
                && $resource->getName() === $name
            ) {
                unset($this->resources[$k]);
                $this->resources = array_values($this->resources);

                return;
            }
        }

        throw new \LogicException(sprintf(
            'Cannot remove resource %s.%s, as it was not found in the state file.',
            $type,
            $name,
        ));
    }

    /**
     * @return Resource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
