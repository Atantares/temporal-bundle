<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle;

use Atantares\TemporalBundle\DependencyInjection\Compiler\ClientCompilerPass;
use Atantares\TemporalBundle\DependencyInjection\Compiler\DoctrineCompilerPass;
use Atantares\TemporalBundle\DependencyInjection\Compiler\ScheduleClientCompilerPass;
use Atantares\TemporalBundle\DependencyInjection\Compiler\WorkflowCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class TemporalBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new WorkflowCompilerPass());
        $container->addCompilerPass(new ClientCompilerPass());
        $container->addCompilerPass(new DoctrineCompilerPass());
        $container->addCompilerPass(new ScheduleClientCompilerPass());
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}