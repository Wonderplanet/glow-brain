<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;

class LogCurrencyPaidFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        LogCurrencyPaid::factory()->create();

        // Verify
        $this->assertTrue(LogCurrencyPaid::query()->exists());
    }
}
