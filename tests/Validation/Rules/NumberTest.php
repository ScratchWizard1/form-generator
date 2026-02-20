<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use FormGenerator\Validation\Rules\Number;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(Number::class)]
final class NumberTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Validation\Rules\Number::class;
}
