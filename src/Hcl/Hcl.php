<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Hcl;

use stdClass;

class Hcl
{
    protected array $data;

    public static function fromJson(string $json): self
    {
        $self = new self();
        $self->data = json_decode($json, true);

        return $self;
    }

    public static function fromData(array|stdClass $data): self
    {
        return self::fromJson(json_encode($data));
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags);
    }

    public function toString(?Schema $schema = null): string
    {

    }
}
