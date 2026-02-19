<?php

namespace Database\Factories;

use App\Domain\Party\Models\Eloquent\UsrParty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Party\Eloquent\Models\UsrParty>
 */
class UsrPartyFactory extends Factory
{
    protected $model = UsrParty::class;

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
            'party_no' => 1,
            'party_name' => 'party 1',
            'usr_unit_id_1' => null,
            'usr_unit_id_2' => null,
            'usr_unit_id_3' => null,
            'usr_unit_id_4' => null,
            'usr_unit_id_5' => null,
            'usr_unit_id_6' => null,
            'usr_unit_id_7' => null,
            'usr_unit_id_8' => null,
            'usr_unit_id_9' => null,
            'usr_unit_id_10' => null,
        ];
    }
}
