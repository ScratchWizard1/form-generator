<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Validation;

use PHPUnit\Framework\TestCase;
use FormGenerator\Validation\Format;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(Format::class)]
final class FormatTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Validation\Format::class;
}
