<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Interceptor;

use Atantares\TemporalBundle\Finalizer\DoctrinePingConnectionFinalizer;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\Exception\EntityManagerClosed;
use Temporal\Interceptor\ActivityInbound\ActivityInput;
use Temporal\Interceptor\ActivityInboundInterceptor;
use Throwable;

final class DoctrineActivityInboundInterceptor implements ActivityInboundInterceptor
{
    public function __construct(
        private readonly DoctrinePingConnectionFinalizer $finalizer,
    ) {}

    /**
     * @throws Throwable
     */
    public function handleActivityInbound(ActivityInput $input, callable $next): mixed
    {
        try {
            $result = $next($input);
        } catch (Throwable $e) {
            if ($e instanceof EntityManagerClosed || $e instanceof DriverException) {
                $this->finalizer->finalize();
            }

            throw $e;
        }

        return $result;
    }
}
