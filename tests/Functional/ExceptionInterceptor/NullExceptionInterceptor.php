<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Test\Functional\ExceptionInterceptor;

use Temporal\Exception\ExceptionInterceptorInterface as ExceptionInterceptor;
use Throwable;

final class NullExceptionInterceptor implements ExceptionInterceptor
{
    public function isRetryable(Throwable $e): bool
    {
        return true;
    }
}
