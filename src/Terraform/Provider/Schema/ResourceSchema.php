<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Provider\Schema;

class ResourceSchema
{
    public function __construct(
        protected Block $block,
        protected int $version,
    ) {}

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getBlock(): Block
    {
        return $this->block;
    }

    public static function fromArray(array $array): self
    {
        return new self(
            Block::fromArray($array['block']),
            $array['version'],
        );
    }
}
