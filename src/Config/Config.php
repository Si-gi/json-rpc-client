<?php
declare(strict_types=1);

namespace JsonRpc\Config;

class Config implements ConfigInterface
{
    private array $data = [];

    public function hydrate(array $data): void
    {
        $this->data = $data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function getEndpoint(): string
    {
        return $this->get('endpoint', '');
    }

    public function getDefaultParams(): array
    {
        return $this->get('defaultParams', []);
    }
}