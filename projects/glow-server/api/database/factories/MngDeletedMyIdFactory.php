<?php

namespace Database\Factories;

use App\Domain\Resource\Mng\Models\MngDeletedMyId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngDeletedMyId>
 */
class MngDeletedMyIdFactory extends Factory
{
    protected $model = MngDeletedMyId::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'my_id' => 'A' . date('YmdHis'),
        ];
    }
}
