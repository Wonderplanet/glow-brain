<?php

namespace Database\Factories;

use App\Domain\Emblem\Enums\EmblemType;
use App\Domain\Resource\Mst\Models\MstEmblem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstEmblem>
 */
class MstEmblemFactory extends Factory
{

    protected $model = MstEmblem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'emblem_type' => EmblemType::EVENT->value,
            'mst_series_id' => '1',
            'asset_key' => '1',
            'release_key' => 1,
        ];
    }
}
