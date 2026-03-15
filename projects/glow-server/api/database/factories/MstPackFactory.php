<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Shop\Enums\MstPackCostType;
use App\Domain\Shop\Enums\SaleCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPack>
 */
class MstPackFactory extends Factory
{

    protected $model = MstPack::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'product_sub_id' => fake()->uuid(),
            'discount_rate' => fake()->numberBetween(1, 99),
            'pack_type' => fake()->randomElement(['Daily', 'Normal']),
            'sale_condition' => fake()->randomElement(array_map(fn ($item) => $item->value, SaleCondition::cases())),
            'sale_condition_value' => null,
            'sale_hours' => fake()->numberBetween(24, 72),
            'tradable_count' => fake()->numberBetween(1, 10),
            'cost_type' => fake()->randomElement(array_map(fn ($item) => $item->value, MstPackCostType::cases())),
            'cost_amount' => fake()->numberBetween(1, 1000),
            'is_recommend' => fake()->numberBetween(0, 1),
            'is_first_time_free' => 0,
            'asset_key' => fake()->uuid(),
            'pack_decoration' => null,
        ];
    }
}
