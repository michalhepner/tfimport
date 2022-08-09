<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command;

class ShowCommand extends AbstractCommand
{
    protected function getProcessArgs(): array
    {
        return ['terraform', 'show', '-no-color'];
    }
}
