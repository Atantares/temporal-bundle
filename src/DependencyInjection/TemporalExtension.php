<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\DependencyInjection;

use Exception;
use ReflectionClass;
use Reflector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

final class TemporalExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('service.php');

        $configuration = new Configuration();

        $container->setParameter('temporal.config', $this->processConfiguration($configuration, $configs));
        $container->registerAttributeForAutoconfiguration(WorkflowInterface::class, workflowConfigurator(...));
        $container->registerAttributeForAutoconfiguration(ActivityInterface::class, activityConfigurator(...));
    }
}


/**
 * @internal
 */
function workflowConfigurator(ChildDefinition $definition, WorkflowInterface $attribute, Reflector $reflector): void
{
    if (!$reflector instanceof ReflectionClass) {
        return;
    }

    $assignWorkers = getWorkers($reflector);
    $attributes    = [];

    if (!empty($assignWorkers)) {
        $attributes['workers'] = $assignWorkers;
    }

    $definition->addTag('temporal.workflow', $attributes);
}


/**
 * @internal
 */
function activityConfigurator(ChildDefinition $definition, ActivityInterface $attribute, Reflector $reflector): void
{
    if (!$reflector instanceof ReflectionClass) {
        return;
    }

    $assignWorkers = getWorkers($reflector);
    $attributes    = ['prefix' => $attribute->prefix];

    if (!empty($assignWorkers)) {
        $attributes['workers'] = $assignWorkers;
    }

    $definition->addTag('temporal.activity', $attributes);
}

