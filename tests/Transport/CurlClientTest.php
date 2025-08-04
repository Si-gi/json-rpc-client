<?php

declare(strict_types=1);

namespace Transport\Tests;

use CurlHandle;
use Prophecy\Argument;
use PHPUnit\Framework\TestCase;
use JsonRpc\Transport\CurlClient;
use Prophecy\PhpUnit\ProphecyTrait;

class CurlClientTest extends TestCase
{
    private string $url = 'https://httpbin.org/post';

    public function testCanInitializeCurl()
    {
        $client = new CurlClient();
        $client->init($this->url);

        $this->assertInstanceOf(CurlClient::class, $client);
        $this->assertInstanceOf(CurlHandle::class, $client->getCurl());
    }

    public function testCanSetSingleOption()
    {
        $client = new CurlClient();
        $client->init($this->url)
            ->setOption(CURLOPT_RETURNTRANSFER, true);

        $this->assertTrue(true); // No exception means success
    }

    public function testCanSetMultipleOptions()
    {
        $client = new CurlClient();
        $client->init($this->url)
            ->setOptions([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode(['hello' => 'world']),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

        $this->assertTrue(true); // just ensure no error
    }

    public function testExecReturnsValidJsonResponse()
    {
        $client = new CurlClient();
        $response = $client->init($this->url)
            ->setOptions([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode(['ping' => 'test']),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ])
            ->exec();

        $this->assertIsString($response);
        $decoded = json_decode($response, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('json', $decoded);
        $this->assertEquals(['ping' => 'test'], $decoded['json']);
    }

    public function testGetInfoReturnsHttp200()
    {
        $client = new CurlClient();
        $client->init($this->url)
            ->setOptions([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode(['ping' => 'test']),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ])
            ->exec();

        $status = $client->getInfo(CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $status);
    }

    public function testGetErrorReturnsEmptyStringOnSuccess()
    {
        $client = new CurlClient();
        $client->init($this->url)
            ->setOptions([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode(['ping' => 'test']),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ])
            ->exec();

        $error = $client->getError();
        $this->assertSame('', $error);
    }

    public function testGetErrorReturnsMessageOnFailure()
    {
        $client = new CurlClient();
        $client->init('http://invalid.localhost') // this will fail
            ->setOption(CURLOPT_RETURNTRANSFER, true)
            ->exec();

        $error = $client->getError();
        $this->assertNotEmpty($error);
    }

    public function testCanCloseCurlManually()
    {
        $client = new CurlClient();
        $client->init($this->url);
        $client->close();

        $this->assertTrue(true); // Just no crash
    }
}