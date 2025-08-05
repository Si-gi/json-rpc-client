<?php

declare(strict_types=1);

namespace JsonRpc\Tests\Transport;

use Exception;
use JsonRpc\Request\Request;
use PHPUnit\Framework\TestCase;
use JsonRpc\Transport\CurlClient;
use Prophecy\PhpUnit\ProphecyTrait;
use JsonRpc\Transport\CurlTransport;
use JsonRpc\Exception\JsonRpcException;
use JsonRpc\Exception\TransportException;
use JsonRpc\Transport\TransportInterface;

class CurlTransportTest extends TestCase{
    use ProphecyTrait;

    public function testThrowTransportExceptionOnEmptyEndpoint(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Empty Endpoint');

        $request = $this->prophesize(Request::class);
        $request->getEndpoint()->willReturn('');
        $client = $this->prophesize(CurlClient::class);
        $transport = new CurlTransport($client->reveal());
        $transport->send($request->reveal());
    }

    public function testShouldSendValidJsonRpcRequestAndReturnCorrectResponse()
    {
        $request = $this->prophesize(Request::class);
        $request->getEndpoint()->willReturn('http://example.com');
        $request->toArray()->willReturn(['method' => 'ping']);

        $client = $this->prophesize(CurlClient::class);

        $client->init('http://example.com')->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('array'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('string'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), true)->willReturn($client->reveal());
        $client->exec()->willReturn(json_encode(['result' => 'pong']));
        $client->getInfo(CURLINFO_HTTP_CODE)->willReturn(200);
        $client->close()->shouldBeCalled();

        $transport = new CurlTransport($client->reveal());
        $result = $transport->send($request->reveal());

        $this->assertEquals(['result' => 'pong'], $result);
    }

    public function testShouldThrowExceptionIfCurlFails()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Simulated error');

        $request = $this->prophesize(Request::class);
        $request->getEndpoint()->willReturn('http://example.com');
        $request->toArray()->willReturn(['method' => 'ping']);

        $client = $this->prophesize(CurlClient::class);

        $client->init('http://example.com')->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('array'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('string'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), true)->willReturn($client->reveal());
        $client->exec()->willReturn(false);
        
        $client->getError()->willReturn('Simulated error');

        $transport = new CurlTransport($client->reveal());
        $transport->send($request->reveal());
    }

    public function testShouldHandleNon200HttpResponsesGracefully()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('HTTP 500 received');

        $request = $this->prophesize(Request::class);
        $request->getEndpoint()->willReturn('http://example.com');
        $request->toArray()->willReturn(['method' => 'ping']);

        $client = $this->prophesize(CurlClient::class);

        $client->init('http://example.com')->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('array'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('string'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), true)->willReturn($client->reveal());
        $client->exec()->willReturn('{"error": "Internal Server Error"}');
        $client->getInfo(CURLINFO_HTTP_CODE)->willReturn(500);
        $client->close()->shouldBeCalled();

        $transport = new CurlTransport($client->reveal());
        $transport->send($request->reveal());
    }

   public function testShouldThrowJsonRpcExceptionOnCurlError()
    {
        $this->expectException(JsonRpcException::class);
        $this->expectExceptionMessage('Network unreachable');

        $request = $this->prophesize(Request::class);
        $request->getEndpoint()->willReturn('http://example.com');
        $request->toArray()->willReturn(['method' => 'ping']);

        $client = $this->prophesize(CurlClient::class);

        $client->init('http://example.com')->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('array'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), \Prophecy\Argument::type('string'))->willReturn($client->reveal());
        $client->setOption(\Prophecy\Argument::type('int'), true)->willReturn($client->reveal());
        $client->exec()->willReturn(false);
        $client->getError()->willReturn('Network unreachable');

        $transport = new CurlTransport($client->reveal());

        try {
            $transport->send($request->reveal());
        } catch (JsonRpcException $e) {
            $this->assertEquals('Network unreachable', $e->getMessage());
            $this->assertIsArray($e->getData());
            $this->assertArrayHasKey('payload', $e->getData());
            throw $e;
        }
    }

}