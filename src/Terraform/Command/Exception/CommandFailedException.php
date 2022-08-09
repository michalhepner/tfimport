<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command\Exception;

use MichalHepner\Tfimport\Terraform\Command\AbstractCommand;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CommandFailedException extends RuntimeException
{
    public function __construct(protected AbstractCommand $command)
    {
        parent::__construct(
            sprintf(
                'Process %s failed with code %d',
                $command->getProcess()->getCommandLine(),
                $command->getProcess()->getExitCode(),
            ),
            0,
            new ProcessFailedException($command->getProcess()),
        );
    }

    public function getCommand(): AbstractCommand
    {
        return $this->command;
    }
}
