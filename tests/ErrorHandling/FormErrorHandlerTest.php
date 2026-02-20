<?php

declare(strict_types=1);

namespace FormGenerator\Tests\ErrorHandling;

use PHPUnit\Framework\TestCase;
use FormGenerator\Config\Globals;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;
use FormGenerator\Config\Config;

#[\PHPUnit\Framework\Attributes\CoversClass(Config::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormGeneratorException::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormErrorHandler::class)]
final class FormErrorHandlerTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\ErrorHandling\FormErrorHandler::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('fatalErrorProvider')]
    public function testFatalError(
        string $userMsg,
        string $devMsg,
        int $code,
        string $id,
        bool $debug,
        array $backtraceCoordinator
    ): void {
        if ($debug) {
            $this->expectException(FormGeneratorException::class);
            $this->expectExceptionMessage($devMsg);
            FormErrorHandler::fatalError($userMsg, $devMsg, $code, $id, true, $backtraceCoordinator);
        } else {
            $result = FormErrorHandler::fatalError($userMsg, $devMsg, $code, $id, false, $backtraceCoordinator);
            $this->assertSame($userMsg, $result['error']);
            $this->assertSame($code, $result['code']);
            $this->assertSame($id, $result['id']);
            $this->assertArrayHasKey(Globals::VALIDATION_ERROR_MARKER, $result);
        }
    }

    public static function fatalErrorProvider(): array
    {
        return [
            'debug true with coords' => ['User message', 'Dev message', 123, 'ERR_ID', true, [10, 20]],
            'debug false with coords' => ['User message', 'Dev message', 123, 'ERR_ID', false, [5, 15]],
            'debug true without coords' => ['User message', 'Dev message', 123, 'ERR_ID', true, [null, 20]],
            'debug false without coords' => ['User message', 'Dev message', 123, 'ERR_ID', false, [5, null]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('exceptionOrFalseProvider')]
    public function testExceptionOrFalse(
        string $devMsg,
        int $code,
        string $id,
        bool $debug,
        array $backtraceCoordinator
    ): void {
        if ($debug) {
            $this->expectException(FormGeneratorException::class);
            $this->expectExceptionMessage($devMsg);
            FormErrorHandler::exceptionOrFalse($devMsg, $code, $id, true, $backtraceCoordinator);
        } else {
            $result = FormErrorHandler::exceptionOrFalse($devMsg, $code, $id, false, $backtraceCoordinator);
            $this->assertFalse($result);
        }
    }

    public static function exceptionOrFalseProvider(): array
    {
        return [
            'debug true with coords' => ['Dev message', 500, 'ID1', true, [3, 7]],
            'debug false with coords' => ['Dev message', 500, 'ID1', false, [1, 0]],
            'debug true without coords' => ['Dev message', 500, 'ID1', true, [3, null]],
            'debug false without coords' => ['Dev message', 500, 'ID1', false, [null, 0]],
        ];
    }


    public function testExceptionAlwaysThrows(): void
    {
        $this->expectException(FormGeneratorException::class);
        $this->expectExceptionMessage('Always fail');
        FormErrorHandler::exception('Always fail', 999, 'ID_ALWAYS', [1, 0]);
    }

    public function testShowErrorOutputsHtml(): void
    {
        $e = new FormGeneratorException('Always fail', 999, 'ID_ALWAYS', '1', 0);
        $error = "<div class='error'"
            . "style='color:red;font-weight:bold;"
            . "padding:10px;"
            . "border-radius:5px;"
            . "background-color:#ffe6e6;"
            . "border: solid 1px red;"
            . "'>Message: "
            . $e->getMessage()
            . " | ID: "
            . $e->getId()
            . " | Code: "
            . $e->getCode()
            . " | File: "
            . $e->getFileOrigin()
            . " | Line: "
            . $e->getLineOrigin()
            . "</div>";
        $this->expectOutputString($error);
        FormErrorHandler::showError($e);
    }
}
