<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;

class UsrStoreAllowanceFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        UsrStoreAllowance::factory()->create();

        // Verify
        $this->assertTrue(UsrStoreAllowance::query()->exists());
    }
}
