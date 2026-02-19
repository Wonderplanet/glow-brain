<?php

namespace Database\Factories;

//use App\Domain\Emblem\Enums\EmblemType;
use App\Domain\Resource\Mst\Models\MstDummyUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstDummyUser>
 */
class MstDummyUserFactory extends Factory
{

    protected $model = MstDummyUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' =>  1,
            'mst_unit_id' => fake()->uuid(),
            'mst_emblem_id' => fake()->uuid(),
            'grade_unit_level_total_count' => 1,
        ];
    }
}
