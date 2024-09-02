<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(Atantares\TemporalBundle\Registry\ActivityRegistry::class);
    $services->set(Atantares\TemporalBundle\WorkflowRuntimeCommand::class)
        ->arg('$activityRegistry', service(Atantares\TemporalBundle\Registry\ActivityRegistry::class))
        ->arg('$workerFactory', service(Temporal\Worker\WorkerFactoryInterface::class))
        ->arg('$workerQueue', '%temporal.worker.queue%')
        ->arg('$kernel', service('kernel'))
        ->tag('console.command');
    $services->alias(Temporal\Client\WorkflowClientInterface::class, Temporal\Client\WorkflowClient::class);
    $services->alias(Temporal\Client\ScheduleClientInterface::class, Temporal\Client\ScheduleClient::class);
};
