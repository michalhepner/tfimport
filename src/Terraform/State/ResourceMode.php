<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\State;

enum ResourceMode: string
{
    case MANAGED = 'managed';
    case DATA = 'data';
}
