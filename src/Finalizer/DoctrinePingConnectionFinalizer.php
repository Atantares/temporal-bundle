<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Finalizer;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

final class DoctrinePingConnectionFinalizer implements FinalizerInterface
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly string $entityManagerName,
    ) {}

    /**
     * @throws DBALException
     */
    public function finalize(): void
    {
        try {
            $entityManager = $this->managerRegistry->getManager($this->entityManagerName);
        } catch (InvalidArgumentException) {
            return;
        }

        if (!$entityManager instanceof EntityManager) {
            return;
        }

        $connection = $entityManager->getConnection();

        try {
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL());
        } catch (DBALException) {
            $connection->close();
            $connection->connect();
        }

        if (!$entityManager->isOpen()) {
            $this->managerRegistry->resetManager($this->entityManagerName);
        }
    }
}
