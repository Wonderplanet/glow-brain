<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstExchangeI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstExchangeI18n>
 */
class MstExchangeI18nFactory extends Factory
{
    protected $model = MstExchangeI18n::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mst_exchange_id' => fake()->uuid(),
            'language' => 'ja',
            'name' => fake()->text(50),
            'asset_key' => fake()->uuid(),
        ];
    }
}
