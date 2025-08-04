<?php

declare(strict_types=1);

namespace JsonRpc\tests\Factory;

use JsonRpc\Config\Config;
use PHPUnit\Framework\TestCase;
use JsonRpc\Factory\MessageFactory;
use JsonRpc\Request\Request;
use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class MessageFactoryTest extends TestCase
{
    use ProphecyTrait;
    #[DataProvider('createRequestDataProvider')]
    public function testCreateRequest(
        string $method,
        array $params,
        int|string $id,
        string $endpoint
    ): void
    {
        $messageFactory = new MessageFactory();
        $request = $messageFactory->createRequest($method, $params, $id, $endpoint);

        $this->assertSame($method, $request->toArray()['method']);
        $this->assertSame($endpoint, $request->getEndpoint());
        $this->assertSame($params, $request->toArray()['params']);

   }

    #[DataProvider('createResponseProvider')]
    public function testCreateResponse(array $responseData): void
    {
        $messageFactory = new MessageFactory();
        $response = $messageFactory->createResponse($responseData);

        $this->assertSame($responseData['result'], $response->getResult());
    }
    public static function createRequestDataProvider(): array{
        return [
            ['get', [], 1, 'http://localhost:8080'],
            ['post',['foo' => 'bar'], 2, 'http://localhost:8080'],
        ];
    }

    public static function createResponseProvider(): array
    {
        return [
            [[
                'jsonrpc' => '2.0',
                'result' => ['foo' => 'bar'],
                'id' => 1
            ]]
            ];
    }
}
