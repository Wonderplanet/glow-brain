<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprProductI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprProductI18n>
 */
class OprProductI18nFactory extends Factory
{
    protected $model = OprProductI18n::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'opr_product_id' => fake()->uuid(),
            'language' => 'ja',
            'asset_key' => fake()->uuid(),
        ];
    }
}
