<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Finalizer;

use Doctrine\DBAL\Connection;
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
            $this->executeDummySql($connection);
        } catch (DBALException) {
            $connection->close();
            // Attempt to reestablish the lazy connection by sending another query.
            $this->executeDummySql($connection);
        }

        if (!$entityManager->isOpen()) {
            $this->managerRegistry->resetManager($this->entityManagerName);
        }
    }

    /**
     * @throws DBALException
     */
    private function executeDummySql(Connection $connection): void
    {
        if (null === $connection->getDatabasePlatform()) {
            throw new \RuntimeException(sprintf('No database platform available for %s', self::class));
        }

        $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL());
    }
}
