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
        $this->jsonrpc = $data['jsonrpc'] ?? "";
        $this->result = $data['result'] ?? null;
        $this->error = $data['error'] ?? null;
        $this->id = $data['id'];

        // @Todo find a good way to manage API Server who doesn't rerspect RPC 2.0 standars
        // if ($this->jsonrpc !== '2.0') {
        //     throw new JsonRpcException('Version JSON-RPC incorrect');
        // }

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