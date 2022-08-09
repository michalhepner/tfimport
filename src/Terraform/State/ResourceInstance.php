<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State;

class ResourceInstance
{
    public function __construct(
        protected int $schemaVersion,
        protected array $attributes = [],
        protected array $sensitiveAttributes = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['schema_version'],
            (array) $data['attributes'],
            (array) $data['sensitive_attributes'],
        );
    }

    public function toArray(): array
    {
        return [
            'schema_version' => $this->schemaVersion,
            'attributes' => $this->attributes,
            'sensitive_attributes' => $this->sensitiveAttributes,
        ];
    }

    public function getSchemaVersion(): int
    {
        return $this->schemaVersion;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function removeAttribute(string $name): void
    {
        unset($this->attributes[$name]);
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function getSensitiveAttributes(): array
    {
        return $this->sensitiveAttributes;
    }

    public function setSensitiveAttribute(string $name, mixed $value): void
    {
        $this->sensitiveAttributes[$name] = $value;
    }

    public function removeSensitiveAttribute(string $name): void
    {
        unset($this->sensitiveAttributes[$name]);
    }

    public function hasSensitiveAttribute(string $name): bool
    {
        return array_key_exists($name, $this->sensitiveAttributes);
    }
}
