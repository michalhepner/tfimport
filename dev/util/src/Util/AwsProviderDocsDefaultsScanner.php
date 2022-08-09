<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Util;

use MichalHepner\Tfimport\Terraform\Provider\Schema\Schema;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AwsProviderDocsDefaultsScanner implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(protected Schema $providerSchema, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function scan(string $dirWithMdFiles): array
    {
        $ret = [];
        foreach ($this->getFilenames($dirWithMdFiles) as $resourceType => $filename) {
            $ret[$resourceType] = $this->parse($filename);
        }

        return $ret;
    }

    protected function getFilenames(string $dirWithMdFiles): array
    {
        $finder = new Finder();
        $finder
            ->in($dirWithMdFiles)
            ->files()
            ->name('*.html.markdown')
        ;

        $ret = [];
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $fileContents = file($file->getPathname());
            $resourceTypeFoundInContents = false;
            foreach ($fileContents as $line) {
                $matches = [];
                if (preg_match('/^# Resource: (?P<resourceName>[a-zA-Z0-9_]+)/', trim($line), $matches)) {
                    $ret[$matches['resourceName']] = $file->getPathname();
                    $resourceTypeFoundInContents = true;
                    break;
                }
            }

            if (!$resourceTypeFoundInContents) {
                $this->logger->warning(sprintf('Failed to determine resource type from file %s', $file->getPathname()));
            }
        }

        return $ret;
    }

    protected function parse(string $filename): ?array
    {
        $lines = explode(PHP_EOL, file_get_contents($filename));

        $argumentReferenceSectionLines = $this->getArgumentReferenceSectionLines($lines);

        if ($argumentReferenceSectionLines === null) {
            $this->logger->warning(sprintf('Failed to find Argument Reference section in file %s', $filename));

            return null;
        }

        print_r($argumentReferenceSectionLines);
        $ret = [];

        return $ret;
    }

    protected function getArgumentReferenceSectionLines(array $lines): ?array
    {
        $sectionStarted = false;

        $filteredLines = [];
        foreach ($lines as $line) {
            if ($sectionStarted && str_starts_with($line, '## ')) {
                break;
            } elseif ($sectionStarted && !str_starts_with($line, '## ')) {
                $filteredLines[] = $line;
            } elseif ($line === '## Argument Reference') {
                $sectionStarted = true;
                $filteredLines[] = $line;
            }
        }

        return count($filteredLines) === 0 ? null : $filteredLines;
    }

}
