<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\LogAllowance;

class LogAllowanceFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        LogAllowance::factory()->create();

        // Verify
        $this->assertTrue(LogAllowance::query()->exists());
    }
}
