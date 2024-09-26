<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Finalizer;

interface FinalizerInterface
{
    public function finalize(): void;
}
