<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryPaidLog;

class LogCurrencyRevertHistoryPaidLogFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        LogCurrencyRevertHistoryPaidLog::factory()->create();

        // Verify
        $this->assertTrue(LogCurrencyRevertHistoryPaidLog::query()->exists());
    }
}
