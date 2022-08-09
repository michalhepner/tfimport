<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Provider\Schema;

class Provider
{
    public function __construct(
        protected Block $block,
        protected int $version,
    ) {}

    public static function fromArray(array $array): self
    {
        return new self(
            Block::fromArray($array['block']),
            $array['version'],
        );
    }

    public function getBlock(): Block
    {
        return $this->block;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
