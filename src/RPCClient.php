<?php
declare(strict_types=1);

namespace JsonRpc;



use JsonRpc\Request\Request;
use JsonRpc\Response\Response;
use JsonRpc\Config\ConfigInterface;
use JsonRpc\Transport\TransportInterface;
use JsonRpc\Factory\MessageFactoryInterface;

class RPCClient
{
    private TransportInterface $transport;
    private ConfigInterface $config;
    private MessageFactoryInterface $factory;
    private int|string $idCounter = 1;

    public function __construct(
        TransportInterface $transport,
        ConfigInterface $config,
        MessageFactoryInterface $factory
    ) {
        $this->transport = $transport;
        $this->config = $config;
        $this->factory = $factory;
    }

    public function call(string $method, array $params = []): mixed
    {
        $request = $this->createRequest($method, $params);
        $rawResponse = $this->sendRequest($request);
        $response = $this->createResponse($rawResponse);

        return $this->extractResult($response);
    }

    protected function createRequest(string $method, array $params): Request
    {
        $mergedParams = array_merge($this->config->getDefaultParams(), $params);
        $endpoint = $this->config->getEndpoint();

        return $this->factory->createRequest($method, $mergedParams, $this->idCounter++, $endpoint);
    }

    protected function sendRequest(Request $request): array
    {
        return $this->transport->send($request);
    }

    protected function createResponse(array $responseData): Response
    {
        return $this->factory->createResponse($responseData);
    }

    protected function extractResult(Response $response): mixed
    {
        return $response->getResult();
    }
}
