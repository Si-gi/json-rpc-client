<?php
declare(strict_types=1);

namespace JsonRpc\Factory;

use JsonRpc\Request\Request;
use JsonRpc\Response\Response;

class MessageFactory implements MessageFactoryInterface
{
    public function createRequest(string $method, array $params, int|string $id, string $endpoint): Request
    {
        return new Request($method, $params, $id, $endpoint);
    }

    public function createResponse(array $responseData): Response
    {
        return new Response($responseData);
    }
}
