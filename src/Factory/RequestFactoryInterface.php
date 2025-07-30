<?php
declare(strict_types=1);

namespace JsonRpc\Factory;

use JsonRpc\Request\Request;

interface RequestFactoryInterface
{
    public function createRequest(string $method, array $params, int|string $id, string $endpoint): Request;
}
