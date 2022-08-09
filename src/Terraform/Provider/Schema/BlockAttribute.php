<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Provider\Schema;

class BlockAttribute
{
    protected mixed $type;
    protected string $descriptionKind = 'plain';
    protected string $description;
    protected bool $required = false;
    protected bool $optional = false;
    protected bool $computed = false;
    protected bool $sensitive = false;

    public function __construct(mixed $type)
    {
        $this->type = $type;
    }

    public function getType(): mixed
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function getOptional(): bool
    {
        return $this->optional;
    }

    public function setOptional(bool $optional): void
    {
        $this->optional = $optional;
    }

    public function getComputed(): bool
    {
        return $this->computed;
    }

    public function setComputed(bool $computed): void
    {
        $this->computed = $computed;
    }

    public function getDescriptionKind(): string
    {
        return $this->descriptionKind;
    }

    public function setDescriptionKind(string $descriptionKind): void
    {
        $this->descriptionKind = $descriptionKind;
    }

    public static function fromArray(array $array): self
    {
        $self = new self($array['type']);

        array_key_exists('description_kind', $array) && $self->descriptionKind = $array['description_kind'];
        array_key_exists('description', $array) && $self->description = $array['description'];
        array_key_exists('required', $array) && $self->required = $array['required'];
        array_key_exists('optional', $array) && $self->optional = $array['optional'];
        array_key_exists('computed', $array) && $self->computed = $array['computed'];
        array_key_exists('sensitive', $array) && $self->sensitive = $array['sensitive'];

        return $self;
    }
}
