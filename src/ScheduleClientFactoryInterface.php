<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle;

use Temporal\Client\ScheduleClientInterface;
use Temporal\DataConverter\DataConverterInterface;

interface ScheduleClientFactoryInterface
{
    public function __invoke(): ScheduleClientInterface;

    public function setOptions(array $options): void;

    public function setAddress(string $address): void;

    public function setDataConverter(DataConverterInterface $converter): void;
}
