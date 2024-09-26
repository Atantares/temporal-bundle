<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\DependencyInjection\Compiler;

use Atantares\TemporalBundle\Finalizer\DoctrinePingConnectionFinalizer;
use Atantares\TemporalBundle\InstalledVersions;
use Atantares\TemporalBundle\Interceptor\DoctrineActivityInboundInterceptor;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface as CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function Atantares\TemporalBundle\DependencyInjection\definition;

final class DoctrineCompilerPass implements CompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        if (!InstalledVersions::willBeAvailable('doctrine/doctrine-bundle', EntityManager::class, [])) {
            return;
        }

        if (!$container->hasParameter('doctrine.entity_managers')) {
            return;
        }

        /** @var array<non-empty-string, non-empty-string> $entityManagers */
        $entityManagers = $container->getParameter('doctrine.entity_managers');

        foreach ($entityManagers as $entityManager => $id) {
            $finalizerId = sprintf('temporal.doctrine_ping_connection_%s.finalizer', $entityManager);

            $container->register($finalizerId, DoctrinePingConnectionFinalizer::class)
                ->setArguments([
                    new Reference('doctrine'),
                    $entityManager,
                ])
                ->addTag('temporal.finalizer')
            ;

            $interceptorId = sprintf('temporal.doctrine_ping_connection_%s_activity_inbound.interceptor', $entityManager);

            $container->register($interceptorId, DoctrineActivityInboundInterceptor::class)
                ->setArguments([
                    definition(DoctrinePingConnectionFinalizer::class)
                        ->setArguments([
                            new Reference('doctrine'),
                            $entityManager,
                        ]),
                ])
            ;
        }
    }
}
