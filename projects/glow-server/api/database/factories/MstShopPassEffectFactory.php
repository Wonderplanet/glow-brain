<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstShopPassEffect;
use App\Domain\Shop\Enums\PassEffectType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstShopPassEffect>
 */
class MstShopPassEffectFactory extends Factory
{
    protected $model = MstShopPassEffect::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mst_shop_pass_id' => fake()->uuid(),
            'effect_type' => PassEffectType::AD_SKIP,
            'effect_value' => null,
            'release_key' => 1,
        ];
    }
}
