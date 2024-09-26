<?php

declare(strict_types=1);

use Atantares\TemporalBundle\DataConverter\SymfonySerializerDataConverter;
use Atantares\TemporalBundle\Finalizer\DoctrineClearEntityManagerFinalizer;
use Atantares\TemporalBundle\InstalledVersions;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\JsonConverter;
use Temporal\Exception\ExceptionInterceptor;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->set('temporal.data_converter', DataConverter::class)
        ->args([
            inline_service(JsonConverter::class),
        ])

        ->set('temporal.exception_interceptor', ExceptionInterceptor::class)
            ->factory([ExceptionInterceptor::class, 'createDefault'])
    ;

    $services->set('temporal.data_converter', DataConverter::class)
        ->args([
                   inline_service(SymfonySerializerDataConverter::class)
                       ->args([
                                  service('serializer'),
                              ]),
               ])
    ;

    if (InstalledVersions::willBeAvailable('doctrine/doctrine-bundle', EntityManager::class)) {
        $services->set('temporal.doctrine_clear_entity_manager.finalizer', DoctrineClearEntityManagerFinalizer::class)
            ->args([service('doctrine')])
            ->tag('temporal.finalizer')
        ;
    }

    if (InstalledVersions::willBeAvailable('symfony/monolog-bundle', Logger::class)) {
        $services->set('monolog.logger.temporal')
            ->parent('monolog.logger')
            ->call('withName', ['temporal'], true)
        ;
    }
};
