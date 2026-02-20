<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use FormGenerator\Validation\Rules\Mime;
use FormGenerator\Tests\Traits\StaticUtilityTestTrait;

#[\PHPUnit\Framework\Attributes\CoversClass(Mime::class)]
final class MimeTest extends TestCase
{
    use StaticUtilityTestTrait;

    protected string $staticUtilityClass = \FormGenerator\Validation\Rules\Mime::class;
}
