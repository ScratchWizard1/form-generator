<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Config;

use PHPUnit\Framework\TestCase;
use FormGenerator\Config\Config;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(Config::class)]
final class ConfigTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Config\Config::class;

    protected function tearDown(): void
    {
        $_ENV = [];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getProvider')]
    public function testGet(string $key, mixed $envValue, mixed $default, mixed $expected): void
    {
        if ($envValue !== null) {
            $_ENV[$key] = $envValue;
        }

        $this->assertSame($expected, Config::get($key, $default));
    }

    public static function getProvider(): array
    {
        return [
            ['KEY1', null, 'default', 'default'],
            ['KEY2', 'env-value', 'default', 'env-value'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getWithFallbackProvider')]
    public function testGetWithFallback(array $keys, array $envValues, mixed $default, mixed $expected): void
    {
        foreach ($envValues as $key => $value) {
            $_ENV[$key] = $value;
        }

        $this->assertSame($expected, Config::getWithFallback($keys, $default));
    }

    public static function getWithFallbackProvider(): array
    {
        return [
            [['A', 'B', 'C'], ['B' => 'value-b'], 'default', 'value-b'],
            [['X', 'Y'], [], 'fallback', 'fallback'],
        ];
    }

    public function testLoadDotenvIsIdempotent(): void
    {
        $reflection = new \ReflectionClass(Config::class);

        $valuesProp = $reflection->getProperty('values');
        $valuesProp->setValue(null, []);

        $loadedProp = $reflection->getProperty('dotenvLoaded');
        $loadedProp->setValue(null, false);

        Config::loadDotenv();
        $valuesAfterFirstLoad = $valuesProp->getValue();

        Config::loadDotenv();
        $valuesAfterSecondLoad = $valuesProp->getValue();
        $loadedAfterSecondLoad = $loadedProp->getValue();

        $this->assertSame($valuesAfterFirstLoad, $valuesAfterSecondLoad);
        $this->assertTrue($loadedAfterSecondLoad);
    }



    #[\PHPUnit\Framework\Attributes\DataProvider('valuesFallbackProvider')]
    public function testGetWithFallbackFromInternalValues(
        array $keys,
        array $internalValues,
        mixed $expected
    ): void {
        $reflection = new \ReflectionClass(Config::class);

        $valuesProperty = $reflection->getProperty('values');
        $valuesProperty->setValue(null, $internalValues);

        $loadedProperty = $reflection->getProperty('dotenvLoaded');
        $loadedProperty->setValue(null, true);

        $this->assertSame($expected, Config::getWithFallback($keys));
    }


    public static function valuesFallbackProvider(): array
    {
        return [
            [['A', 'B'], ['B' => 'value-b'], 'value-b'],
            [['X', 'Y', 'Z'], ['Z' => 'value-z'], 'value-z'],
            [['FIRST', 'SECOND'], ['FIRST' => 'first-value'], 'first-value'],
        ];
    }
}
