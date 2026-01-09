<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Resource\Mst\Models\OprCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprCampaign>
 */
class OprCampaignFactory extends Factory
{
    protected $model = OprCampaign::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'campaign_type' => CampaignType::STAMINA->value,
            'target_type' => 'NormalQuest',
            'difficulty' => 'Normal',
            'target_id_type' => 'Quest',
            'target_id' => 'quest1',
            'effect_value' => 100,
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
        ];
    }
}
