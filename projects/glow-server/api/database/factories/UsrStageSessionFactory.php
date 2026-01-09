<?php

namespace Database\Factories;

use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\UsrStageSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Stage\Eloquent\Models\UsrStageSession>
 */
class UsrStageSessionFactory extends Factory
{

    protected $model = UsrStageSession::class;

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
            'mst_stage_id' => fake()->uuid(),
            'is_valid' => StageSessionStatus::CLOSED,
            'party_no' => 0, // 0は無効なパーティ番号
            'continue_count' => 0,
            'daily_continue_ad_count' => 0,
            'auto_lap_count' => 1,
            'latest_reset_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
        ];
    }
}
