<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryFreeLog;

class LogCurrencyRevertHistoryFreeLogFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        LogCurrencyRevertHistoryFreeLog::factory()->create();

        // Verify
        $this->assertTrue(LogCurrencyRevertHistoryFreeLog::query()->exists());
    }
}
