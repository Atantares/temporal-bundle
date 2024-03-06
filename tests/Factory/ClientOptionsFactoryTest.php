<?php

declare(strict_types=1);

namespace Tests\Atantares\TemporalBundle\Factory;

use Atantares\TemporalBundle\Factory\ClientOptionsFactory;
use PHPUnit\Framework\TestCase;

class ClientOptionsFactoryTest extends TestCase
{
    /**
     * @dataProvider data
     */
    public function testCreateFromArray(string $namespace, string $identity, int $queryRejectionCondition): void
    {
        $factory = new ClientOptionsFactory();

        $options = $factory->createFromArray([
            'namespace'                 => $namespace,
            'identity'                  => $identity,
            'query-rejection-condition' => $queryRejectionCondition,
        ]);

        $this->assertEquals($namespace, $options->namespace);
        $this->assertEquals($identity, $options->identity);
        $this->assertEquals($queryRejectionCondition, $options->queryRejectionCondition);
    }

    public static function data(): array
    {
        return [
            ['namespace', 'identity', 1],
            ['', 'another', 2],
        ];
    }
}
