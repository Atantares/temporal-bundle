<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Test\Functional\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Atantares\TemporalBundle\Test\Functional\Activity\ActivityAHandler;
use Atantares\TemporalBundle\Test\Functional\Activity\ActivityBHandler;
use Atantares\TemporalBundle\Test\Functional\Activity\ActivityCHandler;

final class TestActivityBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->register(ActivityAHandler::class);
        $container->register(ActivityBHandler::class);
        $container->register(ActivityCHandler::class);
    }
}
