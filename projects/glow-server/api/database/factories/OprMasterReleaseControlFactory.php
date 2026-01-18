<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprMasterReleaseControl;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprMasterReleaseControl>
 */
class OprMasterReleaseControlFactory extends Factory
{
    protected $model = OprMasterReleaseControl::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'git_revision' => 'test_git_revision',
            'release_at' => '2024-01-01 00:00:00',
            'release_description' => 'test_release_description',
            'client_data_hash' => 'test_client_data_hash',
            'zh-Hant_client_i18n_data_hash' => 'test_zh-Hant_client_i18n_data_hash',
            'en_client_i18n_data_hash' => 'test_en_client_i18n_data_hash',
            'ja_client_i18n_data_hash' => 'test_ja_client_i18n_data_hash',
            'client_opr_data_hash' => 'test_client_opr_data_hash',
            'zh-Hant_client_opr_i18n_data_hash' => 'test_zh-Hant_client_opr_i18n_data_hash',
            'en_client_opr_i18n_data_hash' => 'test_en_client_opr_i18n_data_hash',
            'ja_client_opr_i18n_data_hash' => 'test_ja_client_opr_i18n_data_hash',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];
    }
}
