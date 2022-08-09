<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Provider\Schema;

class Block
{
    protected ?array $attributes = null;
    protected ?array $blockTypes = null;
    protected string $descriptionKind = 'plain';

    public function __construct(
        ?array $attributes = null,
        ?array $blockTypes = null,
    ) {
        if ($attributes !== null) {
            $this->attributes = array_map(fn (BlockAttribute $blockAttribute) => $blockAttribute, $attributes);
        }

        if ($blockTypes !== null) {
            $this->blockTypes = array_map(fn (BlockType $blockType) => $blockType, $blockTypes);
        }
    }

    public function getDescriptionKind(): string
    {
        return $this->descriptionKind;
    }

    /**
     * @return BlockAttribute[]|null
     */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name): ?BlockAttribute
    {
        return $this->attributes && array_key_exists($name, $this->attributes) ?
            $this->attributes[$name] :
            null
        ;
    }

    /**
     * @return BlockType[]|null
     */
    public function getBlockTypes(): ?array
    {
        return $this->blockTypes;
    }

    public function getBlockType(string $name): ?BlockType
    {
        return $this->blockTypes && array_key_exists($name, $this->blockTypes) ?
            $this->blockTypes[$name] :
            null
        ;
    }

    public static function fromArray(array $array): self
    {
        $self = new self(
            !array_key_exists('attributes', $array) ? null : array_map(
                fn (array $attribute) => BlockAttribute::fromArray($attribute),
                $array['attributes'],
            ),
            !array_key_exists('block_types', $array) ? null : array_map(
                fn (array $blockType) => BlockType::fromArray($blockType),
                $array['block_types'],
            ),
        );

        $self->descriptionKind = $array['description_kind'];

        return $self;
    }
}
