<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use FormGenerator\Validation\Rules\Json;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(Json::class)]
final class JsonTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Validation\Rules\Json::class;
}
