<?php

namespace Database\Factories;

use App\Domain\Message\Enums\MngMessageType;
use App\Domain\Resource\Mng\Models\MngMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngMessage>
 */
class MngMessageFactory extends Factory
{

    protected $model = MngMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'start_at' => '2000-01-01 00:00:00',
            'expired_at' => '2038-01-01 00:00:00',
            'type' => MngMessageType::ALL->value,
            'account_created_start_at' => '2000-01-01 00:00:00',
            'account_created_end_at' => '2038-01-01 00:00:00',
            'add_expired_days' => 0,
        ];
    }
}
