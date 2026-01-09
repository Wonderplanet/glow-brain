<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;

class UsrStoreProductHistoryFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        UsrStoreProductHistory::factory()->create();

        // Verify
        $this->assertTrue(UsrStoreProductHistory::query()->exists());
    }
}
