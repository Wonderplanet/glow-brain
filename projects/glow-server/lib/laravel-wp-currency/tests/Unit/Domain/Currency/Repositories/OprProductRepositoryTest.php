<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\OprProduct;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;

class OprProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OprProductRepository $oprProductRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->oprProductRepository = $this->app->make(OprProductRepository::class);
    }

    #[Test]
    public function findById_指定したIDでデータを取得()
    {
        // Setup
        OprProduct::factory()->create([
            'id' => '1',
            'mst_store_product_id' => 'mst_product1',
            'paid_amount' => 10,
            'display_priority' => 1,
            'release_key' => 0,
            'start_date' => '2024-01-01 00:00:00',
            'end_date' => '2024-01-31 00:00:00',
        ]);

        // Exercise
        $oprProduct = $this->oprProductRepository->findById('1');

        // Verify
        $this->assertEquals('1', $oprProduct->id);
        $this->assertEquals('mst_product1', $oprProduct->mst_store_product_id);
        $this->assertEquals(10, $oprProduct->paid_amount);
    }
}
