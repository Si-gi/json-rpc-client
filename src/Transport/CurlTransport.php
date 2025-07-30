<?php

declare(strict_types=1);

namespace JsonRpc\Transport;



use JsonRpc\Request\Request;
use JsonRpc\Exception\TransportException;

class CurlTransport implements TransportInterface
{

    public function send(Request $request): array
    {
        $endpoint = $request->getEndpoint();
        if (empty($endpoint)) {
            throw new TransportException("Aucun endpoint fourni dans la requête");
        }

        $payload = json_encode($request->toArray(), JSON_THROW_ON_ERROR);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        if (false === $response) {
            throw new TransportException(curl_error($ch));
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            throw new TransportException("HTTP $code reçu");
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}