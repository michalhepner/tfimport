<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State\ResourceAttributeFilter;

use MichalHepner\Tfimport\Terraform\Provider\Schema\BlockAttribute;
use MichalHepner\Tfimport\Terraform\Provider\Schema\ResourceSchema;
use MichalHepner\Tfimport\Terraform\Provider\Schema\Schema;

class AwsTagsAllFilter implements FilterInterface
{
    public function shouldFilter(
        string $attributeName,
        mixed $attributeValue,
        BlockAttribute $attributeSchema,
        string $resourceType,
        ResourceSchema $resourceSchema,
        string $providerName,
        Schema $providerSchema
    ): bool {
        return $providerName === 'registry.terraform.io/hashicorp/aws' && $attributeName == 'tags_all';
    }
}
