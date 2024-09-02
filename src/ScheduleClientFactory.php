<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle;

use Atantares\TemporalBundle\Factory\ClientOptionsFactory;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\ScheduleClient;
use Temporal\Client\ScheduleClientInterface;
use Temporal\DataConverter\DataConverterInterface;

final class ScheduleClientFactory implements ScheduleClientFactoryInterface
{
    private DataConverterInterface $dataConverter;
    private ClientOptions $options;
    private string $address;

    public function __invoke(): ScheduleClientInterface
    {
        return ScheduleClient::create(
            serviceClient: ServiceClient::create($this->address),
            options: $this->options,
            converter: $this->dataConverter
        );
    }

    public function setOptions(array $options): void
    {
        $this->options = (new ClientOptionsFactory())->createFromArray($options);
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function setDataConverter(DataConverterInterface $converter): void
    {
        $this->dataConverter = $converter;
    }
}
