<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class UsrCurrencySummaryFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        UsrCurrencySummary::factory()->create();

        // Verify
        $this->assertTrue(UsrCurrencySummary::query()->exists());
    }
}
