<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Serializer;

use Symfony\Component\Serializer\Serializer;

interface SerializerFactory
{
    public function create(): Serializer;
}