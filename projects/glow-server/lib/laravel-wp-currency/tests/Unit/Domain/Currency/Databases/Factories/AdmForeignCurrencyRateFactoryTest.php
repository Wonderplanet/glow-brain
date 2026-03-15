<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;

class AdmForeignCurrencyRateFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        AdmForeignCurrencyRate::factory()->create();

        // Verify
        $this->assertTrue(AdmForeignCurrencyRate::query()->exists());
    }
}
