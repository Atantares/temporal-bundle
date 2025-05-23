<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\DependencyInjection\Compiler;

use Atantares\TemporalBundle\Command\ScheduleClientDebugCommand;
use Atantares\TemporalBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface as CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClient as GrpcServiceClient;
use Temporal\Client\GRPC\ServiceClientInterface as ServiceClient;
use Temporal\Client\ScheduleClient as GrpcScheduleClient;
use Temporal\Client\ScheduleClientInterface as ScheduleClient;

/**
 * @phpstan-import-type RawConfiguration from Configuration
 */
final class ScheduleClientCompilerPass implements CompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        /** @var RawConfiguration $config */
        $config  = $container->getParameter('temporal.config');
        $clients = [];

        foreach ($config['scheduleClients'] as $name => $client) {
            $options = (new Definition(ClientOptions::class))
                ->addMethodCall('withNamespace', [$client['namespace']], true);

            if ($client['identity'] ?? false) {
                $options->addMethodCall('withIdentity', [$client['identity']], true);
            }

            if (array_key_exists('queryRejectionCondition', $client)) {
                $options->addMethodCall('withQueryRejectionCondition', [$client['queryRejectionCondition']], true);
            }


            $id = sprintf('temporal.%s.schedule_client', $name);

            $container->register($id, ScheduleClient::class)
                ->setFactory([GrpcScheduleClient::class, 'create'])
                ->setArguments([
                    '$serviceClient' => (new Definition(ServiceClient::class, [$client['address']]))
                        ->setFactory([GrpcServiceClient::class, 'create']),

                    '$options'   => $options,
                    '$converter' => new Reference($client['dataConverter']),
                ]);

            if ($name === $config['defaultClient']) {
                $container->setAlias(ScheduleClient::class, $id);
            }

            $container->registerAliasForArgument($id, ScheduleClient::class, sprintf('%sScheduleClient', $name));


            $clients[] = [
                'id'            => $id,
                'name'          => $name,
                'options'       => $options,
                'dataConverter' => $client['dataConverter'],
                'address'       => $client['address'],
            ];
        }

        $container->register('temporal.schedule_client_debug.command', ScheduleClientDebugCommand::class)
            ->setArguments([
                '$clients' => $clients,
            ])
            ->addTag('console.command')
        ;
    }
}
