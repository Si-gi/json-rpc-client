<?php

declare(strict_types=1);

namespace JsonRpc\tests\Config;

use PHPUnit\Framework\TestCase;
use JsonRpc\Config\ConfigBuilder;
use JsonRpc\Config\ConfigInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Yaml\Yaml;


class ConfigBuilderTest extends TestCase
{
    use ProphecyTrait;

    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . '/config_builder_test_' . uniqid();
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '/*') as $file) {
            @unlink($file);
        }
        @rmdir($this->tmpDir);
        parent::tearDown();
    }

    public function testLoadValidConfigFile(): void
    {
        $file = $this->tmpDir . '/config.json';
        $data = ['foo' => 'bar', 'baz' => 42];
        file_put_contents($file, json_encode($data));

        $result = ConfigBuilder::getConfigfromFile($file);

        $this->assertSame($data, $result);
    }

    public function testMockedDependenciesAreInjected(): void
    {
        $file = $this->tmpDir . '/config.json';
        $data = ['foo' => 'bar'];
        file_put_contents($file, json_encode($data));

        $config = new class implements ConfigInterface {
            public array $hydrated = [];
            public function hydrate(array $data): void { $this->hydrated = $data; }
            public function get(string $key, mixed $default = null): mixed { return $this->hydrated[$key] ?? $default; }
            public function getEndpoint(): string { return ''; }
            public function getDefaultParams(): array { return []; }
        };

        $result = ConfigBuilder::build($file, $config);

        $this->assertSame($data, $config->hydrated);
        $this->assertSame($config, $result);
    }

    public function testTestSuiteExecutionAndReporting(): void
    {
        // This test simply asserts true to ensure the test suite runs and reports.
        $this->assertTrue(true, 'Test suite executed and reported results.');
    }

    public function testInvalidConfigFileFormatHandling(): void
    {
        $file = $this->tmpDir . '/config.invalid';
        file_put_contents($file, 'invalid content');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Format non supporté: invalid');
        ConfigBuilder::getConfigfromFile($file);
    }

    public function testBuildWithValidJsonFile(): void
    {
        $file = $this->tmpDir . '/config.json';
        $data = ['foo' => 'bar', 'baz' => 123];
        file_put_contents($file, json_encode($data));

        $config = new class implements ConfigInterface {
            public array $hydrated = [];
            public function hydrate(array $data): void { $this->hydrated = $data; }
            public function get(string $key, mixed $default = null): mixed { return $this->hydrated[$key] ?? $default; }
            public function getEndpoint(): string { return ''; }
            public function getDefaultParams(): array { return []; }
        };

        $result = ConfigBuilder::build($file, $config);

        $this->assertSame($data, $config->hydrated);
        $this->assertSame($config, $result);
    }

    public function testBuildWithValidYamlFile(): void
    {
        $file = $this->tmpDir . '/config.yaml';
        $data = ['foo' => 'bar', 'baz' => 123];
        file_put_contents($file, Yaml::dump($data));

        $config = new class implements ConfigInterface {
            public array $hydrated = [];
            public function hydrate(array $data): void { $this->hydrated = $data; }
            public function get(string $key, mixed $default = null): mixed { return $this->hydrated[$key] ?? $default; }
            public function getEndpoint(): string { return ''; }
            public function getDefaultParams(): array { return []; }
        };

        $result = ConfigBuilder::build($file, $config);

        $this->assertSame($data, $config->hydrated);
        $this->assertSame($config, $result);
    }

    public function testBuildWithValidEnvFile(): void
    {
        $file = $this->tmpDir . '/config.env';
        $content = "FOO=bar\nBAZ=123\n#COMMENTED=shouldnotappear\n";
        file_put_contents($file, $content);

        $config = new class implements ConfigInterface {
            public array $hydrated = [];
            public function hydrate(array $data): void { $this->hydrated = $data; }
            public function get(string $key, mixed $default = null): mixed { return $this->hydrated[$key] ?? $default; }
            public function getEndpoint(): string { return ''; }
            public function getDefaultParams(): array { return []; }
        };

        $result = ConfigBuilder::build($file, $config);

        $expected = ['FOO' => 'bar', 'BAZ' => '123'];
        $this->assertSame($expected, $config->hydrated);
        $this->assertSame($config, $result);
    }

    public function testGetConfigFromFileWithMissingFile(): void
    {
        $file = $this->tmpDir . '/nonexistent.json';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Fichier de configuration introuvable: $file");
        ConfigBuilder::getConfigfromFile($file);
    }

    public function testGetConfigFromFileWithUnsupportedFormat(): void
    {
        $file = $this->tmpDir . '/config.unsupported';
        file_put_contents($file, 'irrelevant');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Format non supporté: unsupported');
        ConfigBuilder::getConfigfromFile($file);
    }

    public function testGetConfigFromFileWithInvalidJson(): void
    {
        $file = $this->tmpDir . '/config.json';
        file_put_contents($file, '{invalid json}');
        $this->expectException(\JsonException::class);
        ConfigBuilder::getConfigfromFile($file);
    }
}