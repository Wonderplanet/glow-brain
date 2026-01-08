<?php

namespace Database\Factories;

use App\Domain\Outpost\Enums\OutpostEnhancementType;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstOutpostEnhancement>
 */
class MstOutpostEnhancementFactory extends Factory
{

    protected $model = MstOutpostEnhancement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_outpost_id' => fake()->uuid(),
            'outpost_enhancement_type' => OutpostEnhancementType::cases()[0]->value,
            'asset_key' => fake()->uuid(),
            'release_key' => 1,
        ];
    }
}
