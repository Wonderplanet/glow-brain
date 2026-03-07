<?php

namespace Database\Factories\Usr;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adm\AdmUser>
 */
class UsrStoreInfoFactory extends Factory
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
            'age' => 0,
            'paid_price' => 0,
            'renotify_at' => null,
        ];
    }
}
