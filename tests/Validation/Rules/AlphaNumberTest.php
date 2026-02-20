<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use FormGenerator\Validation\Rules\AlphaNumber;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(AlphaNumber::class)]
final class AlphaNumberTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Validation\Rules\AlphaNumber::class;
}
