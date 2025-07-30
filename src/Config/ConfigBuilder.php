<?php

declare(strict_types=1);

namespace JsonRpc\Config;

use Symfony\Component\Yaml\Yaml;

class ConfigBuilder
{
    public static function fromFile(string $path, ConfigInterface $config): ConfigInterface
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Fichier de configuration introuvable: $path");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $data = match ($extension) {
            'json' => json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR),
            'yaml', 'yml' => Yaml::parseFile($path),
            'env' => self::parseEnv($path),
            default => throw new \RuntimeException("Format non supporté: $extension")
        };

        $config->hydrate($data);
        return $config;
    }

    private static function parseEnv(string $file): array
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
        return $env;
    }
}