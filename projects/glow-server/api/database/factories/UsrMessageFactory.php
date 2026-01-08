<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Models\UsrMessage as ModelsUsrMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Message\Models\UsrMessage>
 */
class UsrMessageFactory extends Factory
{

    protected $model = UsrMessage::class;

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
            'message_source' => 'MngMessage',
            'reward_group_id' => null,
            'opened_at' => null,
            'is_received' => false,
            'expired_at' => null,
        ];
    }

    public function createAndConvert(array $attributes = [], ?UsrMessage $parent = null): ModelsUsrMessage
    {
        return ModelsUsrMessage::createFromRecord(
            (object) $this->create($attributes, $parent)->toArray(),
        );
    }
}
