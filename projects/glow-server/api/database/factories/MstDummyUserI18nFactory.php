<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstDummyUserI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstDummyUserI18n>
 */
class MstDummyUserI18nFactory extends Factory
{
    protected $model = MstDummyUserI18n::class;

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
            'mst_dummy_user_id' => fake()->uuid(),
            'name' => '',
        ];
    }
}
