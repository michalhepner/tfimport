<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command;

class ProvidersSchemaCommand extends AbstractCommand
{
    protected function getProcessArgs(): array
    {
        return ['terraform', 'providers', 'schema', '-json'];
    }
}
