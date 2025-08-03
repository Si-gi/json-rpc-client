<?php

declare(strict_types=1);

namespace JsonRpc\tests\Config;

use JsonRpc\Config\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ConfigTest extends TestCase
{
    use ProphecyTrait;
    
    public function testGetDefaultParams(): void
    {
        $config = new Config();
        $this->assertEquals([], $config->getDefaultParams());

        $config->hydrate(['defaultParams' => ['foo' => 'bar']]);
        $this->assertEquals(['foo' => 'bar'], $config->getDefaultParams());
    }

    #[DataProvider('HydratorDataProvider')]
    public function testGet(array $data, string $key, mixed $expected): void
    {
        $config = new Config();
        $config->hydrate($data);
        $this->assertEquals($expected, $config->get($key));
    }

    public function testGetEndPoint(): void
     {
        $config = new Config();
        $this->assertEquals('', $config->getEndpoint());

        $config->hydrate(['endpoint' => 'foo']);
        $this->assertEquals('foo', $config->getEndpoint());

     }

    public static function HydratorDataProvider(): array
    {
        return [
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => 'bar'], 'bar', null],
        ];
    }
}