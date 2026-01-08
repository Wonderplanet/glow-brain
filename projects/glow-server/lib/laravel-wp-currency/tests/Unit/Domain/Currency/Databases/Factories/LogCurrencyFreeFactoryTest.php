<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;

class LogCurrencyFreeFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        LogCurrencyFree::factory()->create();

        // Verify
        $this->assertTrue(LogCurrencyFree::query()->exists());
    }
}
