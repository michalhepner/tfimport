<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State;

class Resource
{
    public function __construct(
        protected ResourceMode $mode,
        protected string $type,
        protected string $name,
        protected string $provider,
        protected array $instances,
    ) {}

    /**
     * @return ResourceInstance[]
     */
    public function getInstances(): array
    {
        return $this->instances;
    }

    public function setInstances(array $instances): void
    {
        $this->instances = $instances;
    }

    public static function fromArray(array $resourceData): self
    {
        return new self(
            ResourceMode::from($resourceData['mode']),
            $resourceData['type'],
            $resourceData['name'],
            $resourceData['provider'],
            array_map(
                fn ($resourceInstanceData) => ResourceInstance::fromArray((array) $resourceInstanceData),
                $resourceData['instances'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'mode' => $this->mode->value,
            'type' => $this->type,
            'name' => $this->name,
            'provider' => $this->provider,
            'instances' => array_map(fn(ResourceInstance $instance) => $instance->toArray(), $this->instances),
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMode(): ResourceMode
    {
        return $this->mode;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
