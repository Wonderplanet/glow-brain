<?php

namespace Database\Factories;

use App\Domain\Item\Models\Eloquent\UsrItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Stage\Eloquent\Models\UsrItem>
 */
class UsrItemFactory extends Factory
{

    protected $model = UsrItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_item_id' => fake()->uuid(),
            'amount' => 0,
        ];
    }
}
