<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\DependencyInjection;

use Atantares\TemporalBundle\Attribute\AssignWorker;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 *
 * @param class-string|null                  $class
 * @param array<int|non-empty-string,mixed>  $arguments
 */
function definition(?string $class = null, array $arguments = []): Definition
{
    return new Definition($class, $arguments);
}

/**
 * @internal
 *
 * @phpstan-pure
 */
function referenceLogger(): Reference
{
    return new Reference('monolog.logger.temporal', Container::IGNORE_ON_INVALID_REFERENCE);
}


/**
 * @internal
 *
 * @param ReflectionClass<object> $reflectionClass
 *
 * @return array<int, non-empty-string>
 */
function getWorkers(ReflectionClass $reflectionClass): array
{
    $workers = array_map(static function (ReflectionAttribute $reflectionAttribute): string {
        return $reflectionAttribute->newInstance()->name;
    }, $reflectionClass->getAttributes(AssignWorker::class));

    return array_unique($workers);
}
