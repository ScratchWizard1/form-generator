<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Traits;

trait StaticUtilityTestTrait
{
    public function testClassIsNotInstantiable(): void
    {
        $this->assertNotEmpty($this->staticUtilityClass, '$staticUtilityClass must be defined.');

        $reflection = new \ReflectionClass($this->staticUtilityClass);

        $this->assertFalse(
            $reflection->isInstantiable(),
            "{$this->staticUtilityClass} should not be instantiable."
        );

        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor, 'Constructor must exist.');
        $this->assertTrue($constructor->isPrivate(), 'Constructor must be private.');
    }

    public function testClassCannotBeInstantiated(): void
    {
        $this->expectException(\Error::class);
        $class = $this->staticUtilityClass;
        new $class();
    }



    public function testAllMethodsAreStatic(): void
    {
        $reflection = new \ReflectionClass($this->staticUtilityClass);

        foreach ($reflection->getMethods() as $method) {
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }

            $this->assertTrue(
                $method->isStatic(),
                sprintf(
                    'Method %s::%s should be static.',
                    $this->staticUtilityClass,
                    $method->getName()
                )
            );
        }
    }
}
