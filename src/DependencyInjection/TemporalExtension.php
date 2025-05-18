<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\DependencyInjection;

use Atantares\TemporalBundle\Attribute\AssignWorker;
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
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('service.php');

        $configuration = new Configuration();

        $container->setParameter('temporal.config', $this->processConfiguration($configuration, $configs));
        $container->registerAttributeForAutoconfiguration(WorkflowInterface::class, $this->workflowConfigurator(...));
        $container->registerAttributeForAutoconfiguration(ActivityInterface::class, $this->activityConfigurator(...));
    }

    private function workflowConfigurator(ChildDefinition $definition, WorkflowInterface $attribute, \Reflector $reflector): void
    {
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }

        $assignWorkers = $this->getWorkers($reflector);
        $attributes    = [];

        if (!empty($assignWorkers)) {
            $attributes['workers'] = $assignWorkers;
        }

        $definition->addTag('temporal.workflow', $attributes);
    }

    private function activityConfigurator(ChildDefinition $definition, ActivityInterface $attribute, \Reflector $reflector): void
    {
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }

        $assignWorkers = $this->getWorkers($reflector);
        $attributes    = ['prefix' => $attribute->prefix];

        if (!empty($assignWorkers)) {
            $attributes['workers'] = $assignWorkers;
        }

        $definition->addTag('temporal.activity', $attributes);
    }

    /**
     * @return array<int, non-empty-string>
     */
    private function getWorkers(\ReflectionClass $reflectionClass): array
    {
        $workers = array_map(static function (\ReflectionAttribute $reflectionAttribute): string {
            return $reflectionAttribute->newInstance()->name;
        }, $reflectionClass->getAttributes(AssignWorker::class));

        return array_unique($workers);
    }
}
