<?php

declare(strict_types=1);

namespace FormGenerator\Tests\ErrorHandling;

use FormGenerator\ErrorHandling\FormGeneratorException;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(FormGeneratorException::class)]
class FormGeneratorExceptionTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('exceptionProvider')]
    public function testToArray(
        string $message,
        int $code,
        string $errorId,
        ?string $fileOrigin,
        ?int $lineOrigin,
        array $expectedArray
    ): void {
        $exception = new FormGeneratorException(
            $message,
            $code,
            $errorId,
            $fileOrigin ?? 'unknown',
            $lineOrigin ?? 0
        );

        $this->assertSame($expectedArray, $exception->toArray());
    }

    public static function exceptionProvider(): array
    {
        return [
            'basic error' => [
                'Test error 1',
                100,
                'ERR100',
                'File1.php',
                10,
                [
                    'message' => 'Test error 1',
                    'code' => 100,
                    'id' => 'ERR100',
                    'file' => 'File1.php',
                    'line' => 10,
                ],
            ],
            'defaults test' => [
                'Test error 2',
                0,
                'ERR200',
                null,
                null,
                [
                    'message' => 'Test error 2',
                    'code' => 0,
                    'id' => 'ERR200',
                    'file' => 'unknown',
                    'line' => 0,
                ],
            ],
            'another error' => [
                'Critical failure',
                500,
                'ERR500',
                'Critical.php',
                99,
                [
                    'message' => 'Critical failure',
                    'code' => 500,
                    'id' => 'ERR500',
                    'file' => 'Critical.php',
                    'line' => 99,
                ],
            ],
        ];
    }
}
