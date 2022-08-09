<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Console\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class InitCommand extends Command
{
    protected LoggerInterface $logger;

    protected function configure(): void
    {
        $this->setName('init');
        $this->setDescription('Initializes a temporary terraform module structure in current directory, required to run the tool');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger = new ConsoleLogger($output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

        $finder = new Finder();
        if ($finder->in(getcwd())->count()) {
            $this->logger->error('Directory is not empty, aborting...');

            return 1;
        }

        $fs = new Filesystem();

        $fs->dumpFile(getcwd() . '/main.tf', $this->getMainTf());

        return 0;
    }

    protected function getMainTf(): string
    {
        return <<<HCL
## Configure your providers below so the tool can access your resources.
#provider "aws" {
#  region     = "eu-west-1"
#  access_key = ""
#  secret_key = ""
#}

terraform {
  required_version = ">= 1.0"

  # Provide information about all your used providers below.
  required_providers {
#    aws = {
#      source  = "hashicorp/aws"
#      version = ">= 3.0"
#    }
  }

  # IMPORTANT!!
  # DO NOT EDIT BELOW BACKEND CONFIGURATION NOR ADD ANY OTHER BACKEND.
  backend "local" {}
}

HCL;
    }
}
