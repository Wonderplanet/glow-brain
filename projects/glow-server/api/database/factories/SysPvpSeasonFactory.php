<?php

namespace Database\Factories;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\SysPvpSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Pvp\Models\SysPvpSeason>
 */
class SysPvpSeasonFactory extends Factory
{
    protected $model = SysPvpSeason::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => 'default_pvp',
            'start_at' => now()->toDateTimeString(),
            'end_at' => now()->addDays(30)->toDateTimeString(),
            'closed_at' => now()->addDays(31)->toDateTimeString(),
        ];
    }
}
