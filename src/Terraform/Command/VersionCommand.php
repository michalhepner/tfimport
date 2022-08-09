<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command;

class VersionCommand extends AbstractCommand
{
    protected function getProcessArgs(): array
    {
        return ['terraform', 'version', '-json'];
    }
}
