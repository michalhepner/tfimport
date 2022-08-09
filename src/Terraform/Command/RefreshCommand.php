<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command;

class RefreshCommand extends AbstractCommand
{
    protected function getProcessArgs(): array
    {
        return ['terraform', 'refresh'];
    }
}
