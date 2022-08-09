<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State;

use MichalHepner\Tfimport\Terraform\Provider\Schema\ResourceSchema;
use MichalHepner\Tfimport\Terraform\Provider\Schema\Schema;
use MichalHepner\Tfimport\Terraform\State\ResourceAttributeFilter\AwsTagsAllFilter;
use MichalHepner\Tfimport\Terraform\State\ResourceAttributeFilter\ComputedFilter;
use MichalHepner\Tfimport\Terraform\State\ResourceAttributeFilter\EmptyOptionalFilter;
use MichalHepner\Tfimport\Terraform\State\ResourceAttributeFilter\IdFilter;
use MichalHepner\Tfimport\Terraform\State\ResourceAttributeFilter\FilterInterface;

class StateFileFilter
{
    /**
     * @var FilterInterface[]
     */
    protected $resourceAttributeFilters;

    public function __construct(protected array $providerSchemas)
    {
        $this->resourceAttributeFilters = [
            new IdFilter(),
            new ComputedFilter(),
            new EmptyOptionalFilter(),
            new AwsTagsAllFilter(),
        ];
    }

    public function filter(StateFile $stateFile): StateFile
    {
        $resourceTypeSchemas = $this->getResourceTypesSchemas($this->providerSchemas);

        foreach ($stateFile->getResources() as $resource) {
            if ($resource->getMode() !== ResourceMode::MANAGED) {
                continue;
            }

            $instances = $resource->getInstances();
            if (count($instances) === 0) {
                continue;
            }

            $providerName = $resourceTypeSchemas[$resource->getType()];

            $instances[0]->setAttributes($this->filterAttributes(
                $instances[0]->getAttributes(),
                $resource->getType(),
                $this->providerSchemas[$providerName]->getResourceSchema($resource->getType()),
                $providerName,
                $this->providerSchemas[$providerName],
            ));
        }

        return $stateFile;
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

    protected function filterAttributes(
        array $attributes,
        string $resourceType,
        ResourceSchema $resourceSchema,
        string $providerName,
        Schema $providerSchema,
    ): array {
        foreach ($attributes as $attributeName => $attributeValue) {
            if ($resourceSchema->getBlock()->getBlockType($attributeName)) {
                // @todo, need to do some filtering here as well
                continue;
            }

            if (!$attributeSchema = $resourceSchema->getBlock()->getAttribute($attributeName)) {
                unset($attributes[$attributeName]);
                continue;
            }

            foreach ($this->resourceAttributeFilters as $filter) {
                if ($filter->shouldFilter(
                    $attributeName,
                    $attributeValue,
                    $attributeSchema,
                    $resourceType,
                    $resourceSchema,
                    $providerName,
                    $providerSchema
                )) {
                    unset($attributes[$attributeName]);
                    break;
                }
            }
        }

        return $attributes;
    }
}
