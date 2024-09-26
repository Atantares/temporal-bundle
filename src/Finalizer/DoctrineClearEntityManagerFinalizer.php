<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Finalizer;

use Doctrine\Persistence\ManagerRegistry;

final class DoctrineClearEntityManagerFinalizer implements FinalizerInterface
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry
    ) {}

    public function finalize(): void
    {
        foreach ($this->managerRegistry->getManagers() as $manager) {
            $manager->clear();
        }
    }
}
