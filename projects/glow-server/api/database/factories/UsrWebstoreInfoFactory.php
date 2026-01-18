<?php

namespace Database\Factories;

use App\Domain\Shop\Models\UsrWebstoreInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Models\UsrWebstoreInfo>
 */
class UsrWebstoreInfoFactory extends Factory
{
    protected $model = UsrWebstoreInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'country_code' => 'JP',
            'os_platform' => null,
            'ad_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
