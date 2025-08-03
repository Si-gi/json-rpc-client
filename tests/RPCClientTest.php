<?php

declare(strict_types=1);

namespace JsonRpc\tests;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use JsonRpc\RPCClient;
use JsonRpc\Config\ConfigInterface;
use JsonRpc\Transport\TransportInterface;
use JsonRpc\Factory\MessageFactoryInterface;
use JsonRpc\Request\Request;
use JsonRpc\Response\Response;
use JsonRpc\Exception\JsonRpcException;
use JsonRpc\Exception\TransportException;

class RPCClientTest extends TestCase
{
    use ProphecyTrait;

    public function testCallReturnsExpectedResult()
    {
        $transport = $this->prophesize(TransportInterface::class);
        $config = $this->prophesize(ConfigInterface::class);
        $factory = $this->prophesize(MessageFactoryInterface::class);

        $defaultParams = ['token' => 'abc'];
        $endpoint = 'http://example.com/api';
        $method = 'sum';
        $params = ['a' => 1, 'b' => 2];
        $mergedParams = array_merge($defaultParams, $params);
        $requestId = 1;
        $expectedResult = 3;
        $rawResponse = [
            'jsonrpc' => '2.0',
            'result' => $expectedResult,
            'id' => $requestId
        ];

        $config->getDefaultParams()->willReturn($defaultParams);
        $config->getEndpoint()->willReturn($endpoint);

        $request = $this->prophesize(Request::class)->reveal();
        $factory->createRequest($method, $mergedParams, $requestId, $endpoint)->willReturn($request);

        $transport->send($request)->willReturn($rawResponse);

        $response = $this->prophesize(Response::class);
        $response->getResult()->willReturn($expectedResult);
        $factory->createResponse($rawResponse)->willReturn($response->reveal());

        $client = new RPCClient(
            $transport->reveal(),
            $config->reveal(),
            $factory->reveal()
        );

        $result = $client->call($method, $params);

        $this->assertEquals($expectedResult, $result);
    }

    public function testCreateRequestMergesDefaultAndProvidedParams()
    {
        $transport = $this->prophesize(TransportInterface::class);
        $config = $this->prophesize(ConfigInterface::class);
        $factory = $this->prophesize(MessageFactoryInterface::class);

        $defaultParams = ['foo' => 'bar'];
        $providedParams = ['baz' => 'qux'];
        $mergedParams = array_merge($defaultParams, $providedParams);
        $endpoint = 'http://endpoint';
        $method = 'testMethod';
        $requestId = 1;

        $config->getDefaultParams()->willReturn($defaultParams);
        $config->getEndpoint()->willReturn($endpoint);

        $request = $this->prophesize(Request::class)->reveal();
        $factory->createRequest($method, $mergedParams, $requestId, $endpoint)->willReturn($request);

        $rawResponse = [
            'jsonrpc' => '2.0',
            'result' => 'ok',
            'id' => $requestId
        ];
        $transport->send($request)->willReturn($rawResponse);

        $response = $this->prophesize(Response::class);
        $response->getResult()->willReturn('ok');
        $factory->createResponse($rawResponse)->willReturn($response->reveal());

        $client = new RPCClient(
            $transport->reveal(),
            $config->reveal(),
            $factory->reveal()
        );

        $client->call($method, $providedParams);

        $factory->createRequest($method, $mergedParams, $requestId, $endpoint)->shouldHaveBeenCalled();
    }

    public function testRequestIdCounterIncrementsOnEachCall()
    {
        $transport = $this->prophesize(TransportInterface::class);
        $config = $this->prophesize(ConfigInterface::class);
        $factory = $this->prophesize(MessageFactoryInterface::class);

        $defaultParams = [];
        $endpoint = 'http://endpoint';
        $method = 'foo';

        $config->getDefaultParams()->willReturn($defaultParams);
        $config->getEndpoint()->willReturn($endpoint);

        $request1 = $this->prophesize(Request::class)->reveal();
        $request2 = $this->prophesize(Request::class)->reveal();

        $factory->createRequest($method, [], 1, $endpoint)->willReturn($request1);
        $factory->createRequest($method, [], 2, $endpoint)->willReturn($request2);

        $rawResponse1 = [
            'jsonrpc' => '2.0',
            'result' => 'first',
            'id' => 1
        ];
        $rawResponse2 = [
            'jsonrpc' => '2.0',
            'result' => 'second',
            'id' => 2
        ];

        $transport->send($request1)->willReturn($rawResponse1);
        $transport->send($request2)->willReturn($rawResponse2);

        $response1 = $this->prophesize(Response::class);
        $response1->getResult()->willReturn('first');
        $factory->createResponse($rawResponse1)->willReturn($response1->reveal());

        $response2 = $this->prophesize(Response::class);
        $response2->getResult()->willReturn('second');
        $factory->createResponse($rawResponse2)->willReturn($response2->reveal());

        $client = new RPCClient(
            $transport->reveal(),
            $config->reveal(),
            $factory->reveal()
        );

        $result1 = $client->call($method, []);
        $result2 = $client->call($method, []);

        $this->assertEquals('first', $result1);
        $this->assertEquals('second', $result2);
    }

    public function testCallThrowsOnJsonRpcErrorResponse()
    {
        $this->expectException(JsonRpcException::class);
        $this->expectExceptionMessage('Some error');

        $transport = $this->prophesize(TransportInterface::class);
        $config = $this->prophesize(ConfigInterface::class);
        $factory = $this->prophesize(MessageFactoryInterface::class);

        $defaultParams = [];
        $endpoint = 'http://endpoint';
        $method = 'foo';
        $requestId = 1;

        $config->getDefaultParams()->willReturn($defaultParams);
        $config->getEndpoint()->willReturn($endpoint);

        $request = $this->prophesize(Request::class)->reveal();
        $factory->createRequest($method, [], $requestId, $endpoint)->willReturn($request);

        $rawResponse = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32603,
                'message' => 'Some error'
            ],
            'id' => $requestId
        ];

        $transport->send($request)->willReturn($rawResponse);

        // The Response constructor will throw the exception
        $factory->createResponse($rawResponse)->will(function () use ($rawResponse) {
            throw new JsonRpcException(
                $rawResponse['error']['message'],
                $rawResponse['error']['code'],
                $rawResponse['error']['data'] ?? null
            );
        });

        $client = new RPCClient(
            $transport->reveal(),
            $config->reveal(),
            $factory->reveal()
        );

        $client->call($method, []);
    }

    public function testCallThrowsOnTransportException()
    {
        $this->expectException(TransportException::class);

        $transport = $this->prophesize(TransportInterface::class);
        $config = $this->prophesize(ConfigInterface::class);
        $factory = $this->prophesize(MessageFactoryInterface::class);

        $defaultParams = [];
        $endpoint = 'http://endpoint';
        $method = 'foo';
        $requestId = 1;

        $config->getDefaultParams()->willReturn($defaultParams);
        $config->getEndpoint()->willReturn($endpoint);

        $request = $this->prophesize(Request::class)->reveal();
        $factory->createRequest($method, [], $requestId, $endpoint)->willReturn($request);

        $transport->send($request)->willThrow(new TransportException('Transport failed'));

        $client = new RPCClient(
            $transport->reveal(),
            $config->reveal(),
            $factory->reveal()
        );

        $client->call($method, []);
    }

    public function testCallThrowsOnInvalidJsonRpcVersion()
    {
        $this->expectException(JsonRpcException::class);
        $this->expectExceptionMessage('Version JSON-RPC incorrect');

        $transport = $this->prophesize(TransportInterface::class);
        $config = $this->prophesize(ConfigInterface::class);
        $factory = $this->prophesize(MessageFactoryInterface::class);

        $defaultParams = [];
        $endpoint = 'http://endpoint';
        $method = 'foo';
        $requestId = 1;

        $config->getDefaultParams()->willReturn($defaultParams);
        $config->getEndpoint()->willReturn($endpoint);

        $request = $this->prophesize(Request::class)->reveal();
        $factory->createRequest($method, [], $requestId, $endpoint)->willReturn($request);

        $rawResponse = [
            'jsonrpc' => '1.0', // Invalid version
            'result' => 'something',
            'id' => $requestId
        ];

        $transport->send($request)->willReturn($rawResponse);

        // The Response constructor will throw the exception
        $factory->createResponse($rawResponse)->will(function () {
            throw new JsonRpcException('Version JSON-RPC incorrect');
        });

        $client = new RPCClient(
            $transport->reveal(),
            $config->reveal(),
            $factory->reveal()
        );

        $client->call($method, []);
    }
}