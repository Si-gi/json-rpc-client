<?php

declare(strict_types=1);

namespace JsonRpc\Transport;



use JsonRpc\Request\Request;
use JsonRpc\Exception\TransportException;

class CurlTransport implements TransportInterface
{

    public function __construct(private CurlClient $curlClient)
    {
        
    }
    public function send(Request $request): array
    {

        $endpoint = $request->getEndpoint();
        if (empty($endpoint)) {
            throw new TransportException("Empty Endpoint", 0, ['request' => $request]);
        }

        $payload = json_encode($request->toArray(), JSON_THROW_ON_ERROR);

        $this->curlClient->init($endpoint)
        ->setOption(CURLOPT_RETURNTRANSFER, true)
        ->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json'])
        ->setOption(CURLOPT_POSTFIELDS, $payload);

        $response = $this->curlClient->exec();

        if (false === $response) {
            throw new TransportException($this->curlClient->getError(), 0, ['endpoint' => $endpoint, 'payload' => $payload]);
        }

        $code = $this->curlClient->getInfo(CURLINFO_HTTP_CODE);
        $this->curlClient->close();

        if ($code < 200 || $code >= 300) {
            throw new TransportException("HTTP $code received", $code, ['response' => $response]);
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}