<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle;

use Closure;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 * @phpstan-type Handler \Closure(non-empty-string,  class-string, array<int, non-empty-string>|array{}): bool
 */
final class InstalledVersions
{
    /**
     * @var Closure|null
     */
    private static ?Closure $handler = null;

    /**
     * @param Closure|null $handler
     */
    public static function setHandler(?Closure $handler = null): void
    {
        self::$handler = $handler;
    }

    /**
     * @param non-empty-string                            $package
     * @param class-string                                $class
     * @param non-empty-array<int, class-string>|array{}  $parentPackages
     */
    public static function willBeAvailable(string $package, string $class, array $parentPackages = []): bool
    {
        $handler = self::$handler;

        if ($handler) {
            return $handler($package, $class, $parentPackages);
        }

        return ContainerBuilder::willBeAvailable($package, $class, $parentPackages);
    }
}
