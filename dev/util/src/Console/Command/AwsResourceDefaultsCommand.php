<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Console\Command;

use GuzzleHttp\Client;
use MichalHepner\Tfimport\Terraform\Provider\SchemaProvider;
use MichalHepner\Tfimport\Util\AwsProviderDocsDefaultsScanner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AwsResourceDefaultsCommand extends Command
{
    protected LoggerInterface $logger;

    protected function configure(): void
    {
        $this->setName('aws-resource-defaults');
        $this->addArgument('docs-dir', InputArgument::REQUIRED);
        $this->addArgument('module-with-aws-provider-dir', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger = new ConsoleLogger($output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

        $schemaProvider = new SchemaProvider(logger: $this->logger);
        $schemas = $schemaProvider->fromModulePath($input->getArgument('module-with-aws-provider-dir'));

        $scanner = new AwsProviderDocsDefaultsScanner($schemas['registry.terraform.io/hashicorp/aws'], logger: $this->logger);
        $data = $scanner->scan($input->getArgument('docs-dir'));

        return 0;
    }
}
