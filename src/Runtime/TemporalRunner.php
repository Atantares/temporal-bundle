<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Runtime;

use Symfony\Component\Runtime\RunnerInterface as Runner;

final class TemporalRunner implements Runner
{
    public function __construct(
        private readonly Runtime $runtime,
    ) {}

    public function run(): int
    {
        $this->runtime->run();

        return 0;
    }
}
