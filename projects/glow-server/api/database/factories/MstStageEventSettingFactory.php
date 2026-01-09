<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstStageEventSettingFactory extends Factory
{
    protected $model = MstStageEventSetting::class;

    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_stage_id' => '1',
            'reset_type' => null,
            'clearable_count' => null,
            'ad_challenge_count' => 0,
            'mst_stage_rule_group_id' => null,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2031-01-01 00:00:00',
            'release_key' => 1,
        ];
    }
}
