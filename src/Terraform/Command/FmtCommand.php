<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command;

class FmtCommand extends AbstractCommand
{
    protected bool $throwExceptionOnFailure = false;

    protected function getProcessArgs(): array
    {
        return ['terraform', 'fmt'];
    }
}
