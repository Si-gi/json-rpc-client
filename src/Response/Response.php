<?php
declare(strict_types=1);

namespace JsonRpc\Response;

use JsonRpc\Exception\JsonRpcException;

class Response
{
    private string $jsonrpc;
    private $result;
    private ?array $error;
    private int|string $id;

    public function __construct(array $data)
    {
        $this->jsonrpc = $data['jsonrpc'];
        $this->result = $data['result'] ?? null;
        $this->error = $data['error'] ?? null;
        var_dump($this->error);
        $this->id = $data['id'];

        if ($this->jsonrpc !== '2.0') {
            throw new JsonRpcException('Version JSON-RPC incorrect');
        }

        if (null !== $this->error) {
            throw new JsonRpcException(
                $this->error['message'],
                $this->error['code'],
                $this->error['data'] ?? null
            );
        }
    }

    public function getResult(): mixed
    {
        return $this->result;
    }
}