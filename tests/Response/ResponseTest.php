<?php

declare(strict_types=1);

namespace JsonRpc\Tests\Response;

use JsonRpc\Response\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use JsonRpc\Exception\JsonRpcException;

class ResponseTest extends TestCase{

    use ProphecyTrait;

    public function testConstructorThrowJsonRpcException(): void
    {
        $data = ["jsonrpc" => "2.0", "result" => "test to result", "error" => ["message" => "Throw error", "code" => 401], "id" => 1];
        $this->expectException(JsonRpcException::class);
        $this->expectExceptionMessage('Throw error');
        new Response($data);
    }
}