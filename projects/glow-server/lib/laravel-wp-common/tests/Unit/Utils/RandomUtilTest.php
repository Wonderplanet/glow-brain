<?php

namespace WonderPlanet\Tests\Unit\Utils;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Utils\RandomUtil;

class RandomUtilTest extends TestCase
{
    #[Test]
    public function randomInt_int値が返却される()
    {
        // Setup
        $min = 1;
        $max = 10;
        $randomUtil = app(RandomUtil::class);

        // Exercise
        $result = $randomUtil->randomInt($min, $max);

        // Verify
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
    }

    #[Test]
    public function randomFloat_float値が返却される()
    {
        // Setup
        $min = 1.0;
        $max = 10.0;
        $randomUtil = app(RandomUtil::class);

        // Exercise
        $result = $randomUtil->randomFloat($min, $max);

        // Verify
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
    }
}
