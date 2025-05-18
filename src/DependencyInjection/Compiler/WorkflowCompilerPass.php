<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\DependencyInjection\Compiler;

use Atantares\TemporalBundle\Command\ActivityDebugCommand;
use Atantares\TemporalBundle\Command\WorkerDebugCommand;
use Atantares\TemporalBundle\Command\WorkflowDebugCommand;
use Atantares\TemporalBundle\DependencyInjection\Configuration;
use Atantares\TemporalBundle\Environment;
use Atantares\TemporalBundle\Runtime\Runtime;
use Spiral\RoadRunner\Environment as RoadRunnerEnvironment;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface as CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Worker\Transport\Goridge;
use Temporal\Worker\Worker;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory;

/**
 * @phpstan-import-type RawConfiguration from Configuration
 */
final class WorkflowCompilerPass implements CompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        /** @var RawConfiguration $config */
        $config = $container->getParameter('temporal.config');

        $factory = $container->register('temporal.worker_factory', WorkerFactory::class)
            ->setFactory([WorkerFactory::class, 'create'])
            ->setArguments([
                new Reference($config['pool']['dataConverter']),
                (new Definition(Goridge::class))
                    ->setFactory([Goridge::class, 'create'])
                    ->setArguments([
                        (new Definition(RoadRunnerEnvironment::class))
                            ->setFactory([Environment::class, 'create'])
                            ->setArguments([
                                ['RR_RPC' => $config['pool']['roadrunnerRPC']],
                            ]),
                    ]),
            ])
            ->setPublic(true);

        $runtimeWorker = $container->resolveEnvPlaceholders($config['runtime']['worker'] ?? null, true);

        $configuredWorkers        = [];
        $activitiesWithoutWorkers = [];
        $workflowsWithoutWorkers  = [];

        foreach ($config['workers'] as $workerName => $worker) {
            if (!empty($runtimeWorker) && $workerName !== $runtimeWorker) {
                continue;
            }

            $options = (new Definition(WorkerOptions::class))->setFactory([WorkerOptions::class, 'new']);

            foreach ($worker as $option => $value) {
                $method = sprintf('with%s', ucfirst($option));

                if (!method_exists(WorkerOptions::class, $method)) {
                    continue;
                }

                $options->addMethodCall($method, [$value], true);
            }

            $newWorker = $container->register(sprintf('temporal.%s.worker', $workerName), Worker::class)
                ->setFactory([$factory, 'newWorker'])
                ->setArguments([
                    $worker['taskQueue'],
                    $options,
                    new Reference($worker['exceptionInterceptor']),
                    (new Definition(SimplePipelineProvider::class))
                        ->setArguments([
                            array_map(static fn (string $id): Reference => new Reference($id), $worker['interceptors']),
                        ]),
                ])
                ->setPublic(true);

            foreach ($container->findTaggedServiceIds('temporal.workflow') as $id => $attributes) {
                $class = $container->getDefinition($id)->getClass();

                if ($class === null) {
                    continue;
                }

                $workerNames = $attributes[0]['workers'] ?? null;

                if ($workerNames === null) {
                    $workflowsWithoutWorkers[] = $class;
                }

                if ($workerNames !== null && !in_array($workerName, $workerNames)) {
                    continue;
                }

                $newWorker->addMethodCall('registerWorkflowTypes', [$class]);
            }

            foreach ($container->findTaggedServiceIds('temporal.activity') as $id => $attributes) {
                $class = $container->getDefinition($id)->getClass();

                if ($class === null) {
                    continue;
                }

                $workerNames = $attributes[0]['workers'] ?? null;

                if ($workerNames === null) {
                    $activitiesWithoutWorkers[] = $class;
                }

                if ($workerNames !== null && !in_array($workerName, $workerNames)) {
                    continue;
                }

                $newWorker->addMethodCall('registerActivity', [
                    $class,
                    new ServiceClosureArgument(new Reference($id)),
                ]);
            }


            foreach ($worker['finalizers'] as $id) {
                $newWorker->addMethodCall('registerActivityFinalizer', [
                    (new Definition(\Closure::class, [[new Reference($id), 'finalize']]))
                        ->setFactory([\Closure::class, 'fromCallable']),
                ]);
            }

            $configuredWorkers[$workerName] = $newWorker;
        }



        $container->register('temporal.runtime', Runtime::class)
            ->setArguments([
                $factory,
                $configuredWorkers,
            ])
            ->setPublic(true);


        $container->register('temporal.worker_debug.command', WorkerDebugCommand::class)
            ->setArguments([
                '$workers' => $configuredWorkers,
            ])
            ->addTag('console.command');

        $container->register('temporal.workflow_debug.command', WorkflowDebugCommand::class)
            ->setArguments([
                '$workers'                 => $configuredWorkers,
                '$workflowsWithoutWorkers' => $workflowsWithoutWorkers,
            ])
            ->addTag('console.command');


        $container->register('temporal.activity_debug.command', ActivityDebugCommand::class)
            ->setArguments([
                '$workers'                  => $configuredWorkers,
                '$activitiesWithoutWorkers' => $activitiesWithoutWorkers,
            ])
            ->addTag('console.command');


        foreach ($container->findTaggedServiceIds('temporal.workflow') as $id => $attributes) {
            $container->removeDefinition($id);
        }
    }
}
