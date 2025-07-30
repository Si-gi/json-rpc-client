<?php
declare(strict_types=1);

namespace JsonRpc\Config;

interface ConfigInterface
{

    public function hydrate(array $data): void;
    public function get(string $key, mixed $default = null): mixed;
    public function getEndpoint(): string;
    public function getDefaultParams(): array;
}