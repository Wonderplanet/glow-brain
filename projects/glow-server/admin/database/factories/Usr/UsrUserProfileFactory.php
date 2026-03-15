<?php

namespace Database\Factories\Usr;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adm\AdmUser>
 */
class UsrUserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // 記載されていないパラメータはDBのデフォルト値を使用する
        return [
            'my_id' => fake()->uuid(),
            'name' => fake()->name(),
            'mst_unit_id' => fake()->uuid(),
        ];
    }
}
