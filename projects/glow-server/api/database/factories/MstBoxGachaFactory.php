<?php

namespace Database\Factories;

use App\Domain\BoxGacha\Enums\BoxGachaLoopType;
use App\Domain\Resource\Mst\Models\MstBoxGacha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstBoxGacha>
 */
class MstBoxGachaFactory extends Factory
{
    protected $model = MstBoxGacha::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_event_id' => fake()->uuid(),
            'loop_type' => BoxGachaLoopType::ALL->value,
            'cost_id' => 'item_ticket',
            'cost_num' => 1,
        ];
    }
}
