<?php

namespace Database\Factories\Usr;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adm\AdmUser>
 */
class UsrUserFactory extends Factory
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
        ];
    }
}
