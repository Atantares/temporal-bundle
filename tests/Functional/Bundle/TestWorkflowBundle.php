<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Test\Functional\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Atantares\TemporalBundle\Test\Functional\Workflow\AssignWorkflowHandler;
use Atantares\TemporalBundle\Test\Functional\Workflow\AssignWorkflowHandlerV2;
use Atantares\TemporalBundle\Test\Functional\Workflow\NullWorkflowHandler;

final class TestWorkflowBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->register(NullWorkflowHandler::class);
        $container->register(AssignWorkflowHandler::class);
        $container->register(AssignWorkflowHandlerV2::class);
    }
}
