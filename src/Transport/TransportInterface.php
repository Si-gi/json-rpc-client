<?php
declare(strict_types=1);

namespace JsonRpc\Transport;



use JsonRpc\Request\Request;

interface TransportInterface
{
    /**
     * send the JSON-RPC request and return the http response (array)
     * @param Request $request
     * @return array
     * @throws Exception\TransportException
     */
    public function send(Request $request): array;
}