<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Database\Factories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\LogStore;

class LogStoreFactoryTest extends TestCase
{
    #[Test]
    public function create()
    {
        // Exercise
        LogStore::factory()->create();

        // Verify
        $this->assertTrue(LogStore::query()->exists());
    }
}
