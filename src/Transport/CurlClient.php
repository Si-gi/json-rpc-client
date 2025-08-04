<?php

declare(strict_types=1);

namespace JsonRpc\Transport;

use CurlHandle;

class CurlClient
{
    private CurlHandle $curl;

    public function __destruct()
    {
        curl_close($this->curl);
    }
    public function init(string $url): self
    {
        $this->curl = curl_init($url);
        return $this;
    }

    public function setOptions(array $options): self
    {
        curl_setopt_array($this->curl, $options);
        return $this;
    }

    public function setOption(int $opt, mixed $params):self
    {
        curl_setopt($this->curl, $opt, $params);
        
        return $this;
    }

    public function exec(): string|bool
    {
        return curl_exec($this->curl);
    }

    public function getInfo(int $option)
    {
        return curl_getinfo($this->curl, $option);
    }

    public function getError(): string
    {
        return curl_error($this->curl);
    }

    public function close(): void
    {
        curl_close($this->curl);
    }

    public function getCurl(): CurlHandle
    {
        return $this->curl;
    }
}
