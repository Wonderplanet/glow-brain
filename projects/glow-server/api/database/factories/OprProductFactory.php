<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprProduct>
 */
class OprProductFactory extends Factory
{

    protected $model = OprProduct::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mst_store_product_id' => fake()->uuid(),
            'product_type' => fake()->randomElement(array_map(fn ($item) => $item->value, ProductType::cases())),
            'purchasable_count' => fake()->numberBetween(1, 5),
            'paid_amount' => fake()->numberBetween(100, 1000),
            'display_priority' => fake()->numberBetween(1, 10),
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00',
        ];
    }

    /**
     * 疎通確認用モックデータを作成
     *
     * @return array
     */
    public function createMockData(): array
    {
        $data = [
            'edmo_pack_160_1_framework' => 100,
            'edmo_pack_480_1_framework' => 300,
            'edmo_pack_1000_1_framework' => 1500,
            'edmo_pack_3000_1_framework' => 5000,
            'edmo_pack_9800_1_framework' => 18000,
        ];
        $models = [];
        foreach($data as $oprProductId => $paidAmount) {
            $models[] = $this->create(
                [
                    'id' => $oprProductId,
                    'product_type' => ProductType::DIAMOND->value,
                    'mst_store_product_id' => $oprProductId,
                    'paid_amount' => $paidAmount,
                ]
            );
        }
        return $models;
    }
}
