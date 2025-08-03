<?php
declare(strict_types=1);

namespace JsonRpc\Request;


class Request
{
    private string $jsonrpc = '2.0';

    public function __construct(
        private string $method,
        private array $params = [],
        private int|string $id = 0,
        private string $endpoint = '')
    {
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