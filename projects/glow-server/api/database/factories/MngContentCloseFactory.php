<?php

namespace Database\Factories;

use App\Domain\Common\Enums\ContentMaintenanceType;
use App\Domain\Resource\Mng\Models\MngContentClose;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngContentClose>
 */
class MngContentCloseFactory extends Factory
{
    protected $model = MngContentClose::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startAt = fake()->dateTimeBetween('-1 week', '+1 week');
        $endAt = fake()->dateTimeBetween($startAt, '+2 weeks');

        return [
            'id' => fake()->uuid(),
            'content_type' => fake()->randomElement(ContentMaintenanceType::cases())->value,
            'start_at' => $startAt->format('Y-m-d H:i:s'),
            'end_at' => $endAt->format('Y-m-d H:i:s'),
            'is_valid' => fake()->randomElement([0, 1]),
        ];
    }
}
