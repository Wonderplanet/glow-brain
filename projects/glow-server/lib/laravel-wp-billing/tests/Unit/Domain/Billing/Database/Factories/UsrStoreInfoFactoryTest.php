<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;

class UsrStoreInfoFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        UsrStoreInfo::factory()->create();

        // Verify
        $this->assertTrue(UsrStoreInfo::query()->exists());
    }
}
