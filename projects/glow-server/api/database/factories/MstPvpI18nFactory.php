<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstPvpI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPvpI18n>
 */
class MstPvpI18nFactory extends Factory
{

    protected $model = MstPvpI18n::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
        ];
    }
}
