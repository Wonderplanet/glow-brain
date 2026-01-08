<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\MstStoreProduct;

class MstStoreProductFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        MstStoreProduct::factory()->create();

        // Verify
        $this->assertTrue(MstStoreProduct::query()->exists());
    }
}
