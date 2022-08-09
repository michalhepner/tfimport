<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Provider\Schema;

class BlockType
{
    protected ?int $maxItems;

    public function __construct(
        protected Block $block,
        protected string $nestingMode,
    ) {}

    public function getBlock(): Block
    {
        return $this->block;
    }

    public function getNestingMode(): string
    {
        return $this->nestingMode;
    }

    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    public static function fromArray(array $array): self
    {
        $self = new self(
            Block::fromArray($array['block']),
            $array['nesting_mode'],
        );

        array_key_exists('max_items', $array) && $self->maxItems = $array['max_items'];

        return $self;
    }
}
