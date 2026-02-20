<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Config;

use DateTimeImmutable;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use FormGenerator\Config\Globals;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
final class GlobalsTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Config\Globals::class;

    protected function setUp(): void
    {
        parent::setUp();
        date_default_timezone_set('Europe/Bratislava');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('regexProvider')]
    public function testSetAndGetRegex(string $input): void
    {
        Globals::setRegex($input);
        $this->assertSame($input, Globals::getRegex());
    }

    public static function regexProvider(): array
    {
        return [
            ['/test/'],
            ['/^abc\d+/i'],
            ['[a-z]{3,5}'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('charsetProvider')]
    public function testSetAndGetCharset(string $input): void
    {
        Globals::setCharset($input);
        $this->assertSame($input, Globals::getCharset());
    }

    public static function charsetProvider(): array
    {
        return [
            ['UTF-8'],
            ['ISO-8859-1'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('arrayProvider')]
    public function testSetAndGetArray(array $input): void
    {
        Globals::setArray($input);
        $this->assertSame($input, Globals::getArray());
    }

    public static function arrayProvider(): array
    {
        return [
            [['a', 'b', 'c']],
            [['x' => 1, 'y' => 2]],
            [['nested' => ['inner' => 5]]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('securityProvider')]
    public function testSetAndGetSecurity(
        array $input,
        string $key,
        bool $expected = true
    ): void {
        Globals::security($input);
        $this->assertSame($expected, Globals::getSecurity($key));
    }

    public static function securityProvider(): array
    {
        return [
            [
                ['a' => true, 'b' => false, 'c' => true],
                "b",
                false
            ],
            [
                ['a' => true, 'b' => false, 'c' => true],
                " ",
                true
            ],
            [
                ['a' => true, 'b' => false, 'c' => true],
                "d",
                true
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('toStrProvider')]
    public function testToStr(mixed $input, string $expected): void
    {
        $this->assertSame($expected, Globals::toStr($input));
    }

    public static function toStrProvider(): array
    {
        return [
            [123, '123'],
            [true, 'true'],
            [false, 'false'],
            [null, ''],
            [['a', 'b'], 'a, b'],
            ['   ', ''],
            [Globals::INTERNAL_EMPTY, ''],
            [new class {
                public function __toString(): string
                {
                    return 'obj';
                }
            }, 'obj'],
            [new class {
            }, ''],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('toArrayProvider')]
    public function testToArray(mixed $input, array $expected): void
    {
        $this->assertSame($expected, Globals::toArray($input));
    }

    public static function toArrayProvider(): array
    {
        return [
            [[1, 2, 3], [1, 2, 3]],
            [new \ArrayIterator(['a' => 1]), ['a' => 1]],
            [new class {
                public $x = 5;
                public $y = 'y';
            }, ['x' => 5, 'y' => 'y']],
            ['{"a":1,"b":2}', ['a' => 1, 'b' => 2]],
            ['string', ['string']],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('escProvider')]
    public function testEsc(mixed $input, string $expected): void
    {
        Globals::setCharset('UTF-8');
        $this->assertSame($expected, Globals::esc($input));
    }

    public static function escProvider(): array
    {
        return [
            ['hello', 'hello'],
            ['<b>bold</b>', '&lt;b&gt;bold&lt;/b&gt;'],
            ['', '[empty]'],
            [Globals::INTERNAL_EMPTY, '[empty]'],
            [['a', 'b'], 'a, b'],
            [true, 'true'],
            [null, 'null'],
            [5, '5'],
            [
                new DateTimeImmutable("1970-01-01", new DateTimeZone("Europe/Bratislava")),
                '[DateTimeImmutable{1970-01-01T00:00:00+01:00}]'
            ],
            [
                new DateTime("1970-01-01", new DateTimeZone("Europe/Bratislava")),
                '[DateTime{1970-01-01T00:00:00+01:00}]'
            ],
            [new class {
                public function __toString(): string
                {
                    return 'OBJ';
                }
            }, 'OBJ'],
            [new class {
            }, '[not convertible]'],
        ];
    }
}
