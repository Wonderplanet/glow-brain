<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprCampaignI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprCampaignI18n>
 */
class OprCampaignI18nFactory extends Factory
{
    protected $model = OprCampaignI18n::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'opr_campaign_id' => fake()->uuid(),
            'language' => 'ja',
            'description' => fake()->text(),
        ];
    }
}
