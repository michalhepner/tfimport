<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Command;

use LogicException;
use MichalHepner\Tfimport\Terraform\Command\Exception\CommandFailedException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class AbstractCommand implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected string $cwd;
    protected int $timeout = 3600;
    protected ?Process $process = null;
    protected bool $throwExceptionOnFailure = true;

    public function __construct(?string $cwd = null, ?int $timeout = null, ?LoggerInterface $logger = null)
    {
        $this->cwd = empty($cwd) ? getcwd() : $cwd;
        if ($timeout !== null) {
            $this->timeout = $timeout;
        }

        $this->logger = $logger ?: new NullLogger();
    }

    public function __invoke(): int
    {
        return $this->run();
    }

    public function run(): int
    {
        if ($this->process) {
            throw new LogicException('Command was already started');
        }

        $this->process = new Process($this->getProcessArgs(), $this->cwd);
        $this->process->setTimeout($this->timeout);

        $this->logger->debug(sprintf('Running process \'%s\'', implode(' ', $this->getProcessArgs())));

        if ($this->process->run() && $this->throwExceptionOnFailure) {
            throw new CommandFailedException($this);
        }

        $this->logger->debug(sprintf(
            'Process \'%s\' exited with code %d',
            implode(' ', $this->getProcessArgs()),
            $this->process->getExitCode(),
        ));

        return $this->process->getExitCode();
    }

    public function getCwd(): string
    {
        return $this->cwd;
    }

    public function setCwd(string $cwd): void
    {
        $this->cwd = $cwd;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getOutput(): string
    {
        $this->ensureCommandRan();

        return $this->process->getOutput();
    }

    public function getErrorOutput(): string
    {
        $this->ensureCommandRan();

        return $this->process->getErrorOutput();
    }

    public function getExitCode(): int
    {
        $this->ensureCommandRan();

        return $this->process->getExitCode();
    }

    protected function ensureCommandRan(): void
    {
        if (!$this->process) {
            throw new LogicException('Run the command first');
        }
    }

    public function getThrowExceptionOnFailure(): bool
    {
        return $this->throwExceptionOnFailure;
    }

    public function setThrowExceptionOnFailure(bool $throwExceptionOnFailure): void
    {
        $this->throwExceptionOnFailure = $throwExceptionOnFailure;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * @return string[]
     */
    abstract protected function getProcessArgs(): array;
}
