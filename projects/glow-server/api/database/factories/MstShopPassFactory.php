<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstShopPass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstShopPass>
 */
class MstShopPassFactory extends Factory
{

    protected $model = MstShopPass::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'opr_product_id' => fake()->uuid(),
            'is_display_expiration' => 1,
            'pass_duration_days' => 30,
            'asset_key' => fake()->uuid(),
            'release_key' => 1,
        ];
    }
}
