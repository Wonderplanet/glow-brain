<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Billing\Models\UsrStoreInfo>
 */
class UsrStoreInfoFactory extends Factory
{
    protected $model = UsrStoreInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->uuid,
            'usr_user_id' => $this->faker->unique()->uuid,
            'age' => 0,
            'paid_price' => 0,
            'renotify_at' => null,
            'total_vip_point' => 0,
        ];
    }
}
