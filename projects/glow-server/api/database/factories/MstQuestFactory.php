<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Stage\Enums\QuestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstQuestFactory extends Factory
{
    protected $model = MstQuest::class;

    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'sort_order' => 1,
            'quest_type' => QuestType::NORMAL->value,
            'mst_event_id' => '1',
            'mst_series_id' => 'dan',
            'asset_key' => '',
            'start_date' => '2021-01-01 00:00:00',
            'end_date' => '2031-01-01 00:00:00',
            'release_key' => 1,
            'quest_group' => null,
            'difficulty' => 'Normal',
        ];
    }
}
