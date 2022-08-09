<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Provider\Schema;

class Schema
{
    /**
     * @var ResourceSchema[]|null
     */
    protected ?array $resourceSchemas;

    /**
     * @var DataSourceSchema[]|null
     */
    protected ?array $dataSourceSchemas;

    public function __construct(
        protected ?Provider $provider,
        ?array $resourceSchemas,
        ?array $dataSourceSchemas,
    ) {
        if ($resourceSchemas !== null) {
            $this->resourceSchemas = array_map(fn (ResourceSchema $schema) => $schema, $resourceSchemas);
        }

        if ($dataSourceSchemas !== null) {
            $this->dataSourceSchemas = array_map(fn (DataSourceSchema $schema) => $schema, $dataSourceSchemas);
        }
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function getResourceSchemas(): ?array
    {
        return $this->resourceSchemas;
    }

    public function getResourceSchema(string $name): ?ResourceSchema
    {
        return $this->resourceSchemas && array_key_exists($name, $this->resourceSchemas) ?
            $this->resourceSchemas[$name] :
            null
        ;
    }

    public function getDataSourceSchemas(): array
    {
        return $this->dataSourceSchemas;
    }

    public function getDataSourceSchema(string $name): ?DataSourceSchema
    {
        return $this->dataSourceSchemas && array_key_exists($name, $this->dataSourceSchemas) ?
            $this->dataSourceSchemas[$name] :
            null
        ;
    }

    public static function fromArray(array $array): self
    {
        return new self(
            !array_key_exists('provider', $array) ? null : Provider::fromArray($array['provider']),
            !array_key_exists('resource_schemas', $array) ? null : array_map(
                fn (array $item) => ResourceSchema::fromArray($item),
                $array['resource_schemas']
            ),
            !array_key_exists('data_source_schemas', $array) ? null : array_map(
                fn (array $item) => DataSourceSchema::fromArray($item),
                $array['data_source_schemas']
            )
        );
    }
}
