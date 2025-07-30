<?php
declare(strict_types=1);

namespace JsonRpc\Request;


class Request
{
    private string $jsonrpc = '2.0';
    private string $method;
    private array $params;
    private int|string $id;
    private string $endpoint = '';

    public function __construct(string $method, array $params = [], int|string $id = 0, string $endpoint)
    {
        $this->method = $method;
        $this->params = $params;
        $this->id = $id;
        $this->endpoint = $endpoint;
    }

    public function toArray(): array
    {
        return [
            'jsonrpc' => $this->jsonrpc,
            'method'  => $this->method,
            'params'  => $this->params,
            'id'      => $this->id,
        ];
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }
}