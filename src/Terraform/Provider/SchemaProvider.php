<?php
declare(strict_types=1);

namespace MichalHepner\Tfimport\Terraform\Provider;

use MichalHepner\Tfimport\Terraform\Command\ProvidersSchemaCommand;
use MichalHepner\Tfimport\Terraform\Provider\Schema\Schema;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class SchemaProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string $modulePath
     *
     * @return Schema[]
     *
     * @throws \JsonException
     */
    public function __invoke(string $modulePath): array
    {
        return $this->fromModulePath($modulePath);
    }

    public function fromModulePath(string $modulePath): array
    {
        $command = new ProvidersSchemaCommand($modulePath, logger: $this->logger);
        $command->run();

        $data = json_decode(trim($command->getOutput()), associative: true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \RuntimeException(sprintf(
                'Failed to parse output from `terraform providers schema -json`. Output: %s',
                strlen($command->getOutput()) > 256 ? substr($command->getOutput(), 0, 256) . '...' : $command->getOutput()
            ));
        }

        return $this->fromData($data);
    }

    public function fromData(array $data): array
    {
        if (!isset($data['format_version']) || $data['format_version'] !== '1.0') {
            throw new \RuntimeException(sprintf(
                'Unsupported format of `terraform providers schema -json` data. Data:\n%s',
                strlen(json_encode($data)) > 256 ? substr(json_encode($data), 0, 256) . '...' : json_encode($data)
            ));
        }

        return array_map(
            fn (array $rawProviderSchema) => Schema::fromArray($rawProviderSchema),
            $data['provider_schemas'],
        );
    }
}
