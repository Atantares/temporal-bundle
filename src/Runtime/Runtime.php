<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Runtime;

use Countable;
use Temporal\Worker\WorkerFactoryInterface as WorkerFactory;
use Temporal\Worker\WorkerInterface as Worker;

final class Runtime implements Countable
{
    /**
     * @param array<int, Worker> $workers
     */
    public function __construct(
        private readonly WorkerFactory $factory,
        private readonly array $workers,
    ) {}

    public function run(): void
    {
        $this->factory->run();
    }

    public function count(): int
    {
        return count($this->workers);
    }
}
