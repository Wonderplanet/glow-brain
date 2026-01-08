<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstFragmentBox;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstItem>
 */
class MstFragmentBoxFactory extends Factory
{

    protected $model = MstFragmentBox::class;

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
            'mst_fragment_box_group_id' => 'string',
            'release_key' => '1',
        ];
    }
}
