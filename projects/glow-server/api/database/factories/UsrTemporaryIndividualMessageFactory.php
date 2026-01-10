<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Message\Models\UsrTemporaryIndividualMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Message\Eloquent\Models\UsrTemporaryIndividualMessage>
 */
class UsrTemporaryIndividualMessageFactory extends Factory
{

    protected $model = UsrTemporaryIndividualMessage::class;

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
            'mng_message_id' => fake()->uuid(),
        ];
    }
}
