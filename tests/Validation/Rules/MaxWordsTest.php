<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use FormGenerator\Validation\Rules\MaxWords;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(MaxWords::class)]
final class MaxWordsTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Validation\Rules\MaxWords::class;
}
