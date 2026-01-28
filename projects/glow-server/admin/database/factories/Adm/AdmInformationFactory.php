<?php

namespace Database\Factories\Adm;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adm\AdmInformation>
 */
class AdmInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'enable' => (int) fake()->boolean(),
            'priority' => fake()->numberBetween(1, 100),
            'status' => 'Active',
            'banner_url' => fake()->imageUrl(),
            'category' => fake()->word(),
            'title' => fake()->sentence(),
            'html' => fake()->paragraph(),
            'html_json' => json_encode(['content' => fake()->paragraph()]),
            'author_adm_user_id' => fake()->numberBetween(1, 100),
            'approval_adm_user_id' => fake()->numberBetween(1, 100),
            'pre_notice_start_at' => now(),
            'start_at' => now(),
            'end_at' => now()->addDays(30),
            'post_notice_end_at' => now()->addDays(60),
        ];
    }
}
