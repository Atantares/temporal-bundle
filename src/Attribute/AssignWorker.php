<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class AssignWorker
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
    ) {
    }
}
