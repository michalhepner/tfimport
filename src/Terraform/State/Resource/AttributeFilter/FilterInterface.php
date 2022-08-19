<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State\Resource\AttributeFilter;

use MichalHepner\Tfimport\Terraform\Provider\Schema\BlockAttribute;
use MichalHepner\Tfimport\Terraform\Provider\Schema\ResourceSchema;
use MichalHepner\Tfimport\Terraform\Provider\Schema\Schema;

interface FilterInterface
{
    public function shouldFilter(
        string $attributeName,
        mixed $attributeValue,
        BlockAttribute $attributeSchema,
        string $resourceType,
        ResourceSchema $resourceSchema,
        string $providerName,
        Schema $providerSchema
    ): bool;
}
