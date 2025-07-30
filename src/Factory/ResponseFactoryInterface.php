<?php
declare(strict_types=1);

namespace JsonRpc\Factory;

use JsonRpc\Response\Response;

interface ResponseFactoryInterface
{
    public function createResponse(array $responseData): Response;
}