<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;

class UsrCurrencyPaidFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        UsrCurrencyPaid::factory()->create();

        // Verify
        $this->assertTrue(UsrCurrencyPaid::query()->exists());
    }
}
