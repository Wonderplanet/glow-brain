<?php

namespace Database\Factories;

use App\Domain\Party\Models\Eloquent\UsrArtworkParty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Party\Models\Eloquent\UsrArtworkParty>
 */
class UsrArtworkPartyFactory extends Factory
{
    protected $model = UsrArtworkParty::class;

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
            'mst_artwork_id_1' => 'mst_artwork_id_1',
            'mst_artwork_id_2' => null,
            'mst_artwork_id_3' => null,
            'mst_artwork_id_4' => null,
            'mst_artwork_id_5' => null,
            'mst_artwork_id_6' => null,
            'mst_artwork_id_7' => null,
            'mst_artwork_id_8' => null,
            'mst_artwork_id_9' => null,
            'mst_artwork_id_10' => null,
        ];
    }
}
