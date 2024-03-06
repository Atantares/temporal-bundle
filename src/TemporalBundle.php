<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Atantares\TemporalBundle\Pass\ActivityCompilerPass;

final class TemporalBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ActivityCompilerPass());
    }
}