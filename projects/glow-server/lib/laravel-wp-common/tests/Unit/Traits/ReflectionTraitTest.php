<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Traits;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Tests\Traits\ReflectionTrait;

class ReflectionTraitTest extends TestCase
{
    use ReflectionTrait;

    private static string $staticProp = 'static';
    private string $instanceProp = 'instance';

    private function privateMethod($a, $b)
    {
        return $a + $b;
    }

    private static function privateStaticMethod($a, $b)
    {
        return $a * $b;
    }

    private const PRIVATE_CONST = 'const_value';

    #[Test]
    public function callMethod_正常系()
    {
        $result = $this->callMethod($this, 'privateMethod', [2, 3]);
        $this->assertSame(5, $result);
    }

    #[Test]
    public function callStaticMethod_正常系()
    {
        $result = $this->callStaticMethod(self::class, 'privateStaticMethod', [2, 4]);
        $this->assertSame(8, $result);
    }

    #[Test]
    public function getProperty_正常系()
    {
        $this->instanceProp = 'foo';
        $value = $this->getProperty($this, 'instanceProp');
        $this->assertSame('foo', $value);
    }

    #[Test]
    public function setProperty_正常系()
    {
        $this->setProperty($this, 'instanceProp', 'bar');
        $this->assertSame('bar', $this->instanceProp);
    }

    #[Test]
    public function getStaticProperty_正常系()
    {
        self::$staticProp = 'abc';
        $value = $this->getStaticProperty(self::class, 'staticProp');
        $this->assertSame('abc', $value);
    }

    #[Test]
    public function setStaticProperty_正常系()
    {
        $this->setStaticProperty(self::class, 'staticProp', 'xyz');
        $this->assertSame('xyz', self::$staticProp);
    }

    #[Test]
    public function getConstrant_正常系()
    {
        $value = $this->getConstrant(self::class, 'PRIVATE_CONST');
        $this->assertSame('const_value', $value);
    }
}
