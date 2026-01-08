<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstShopPassI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstShopPassI18n>
 */
class MstShopPassI18nFactory extends Factory
{

    protected $model = MstShopPassI18n::class;
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
            'language' => 'ja',
            'name' => 30,
            'release_key' => 1,
        ];
    }
}
