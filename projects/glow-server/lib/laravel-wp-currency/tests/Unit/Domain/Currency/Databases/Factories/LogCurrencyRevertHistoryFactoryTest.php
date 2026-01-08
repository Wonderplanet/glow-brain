<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistory;

class LogCurrencyRevertHistoryFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        LogCurrencyRevertHistory::factory()->create();

        // Verify
        $this->assertTrue(LogCurrencyRevertHistory::query()->exists());
    }
}
