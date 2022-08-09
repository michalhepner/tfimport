<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command;

class InitCommand extends AbstractCommand
{
    protected function getProcessArgs(): array
    {
        return ['terraform', 'init'];
    }
}
