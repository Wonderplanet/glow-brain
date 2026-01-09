<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitFragmentConvert>
 */
class MstUnitFragmentConvertFactory extends Factory
{

    protected $model = MstUnitFragmentConvert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'unit_label' => 'DropR',
            'convert_amount' => 1,
            'release_key' => 1,
        ];
    }
}
