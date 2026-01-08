<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngMasterReleaseVersion>
 */
class MngMasterReleaseVersionFactory extends Factory
{
    protected $model = MngMasterReleaseVersion::class;

    /**
     * Define the model's default state.
     * MEMO 万が一uuidが重複した場合を想定し接頭辞を付与している
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'git_revision' => 'git_' . fake()->uuid(),
            'master_schema_version' => 'schema_' . fake()->uuid(),
            'data_hash' => 'data_' . fake()->uuid(),
            'server_db_hash' => 'server_db_' . fake()->uuid(),
            'client_mst_data_hash' => 'mst_' . fake()->uuid(),
            'client_mst_data_i18n_ja_hash' => 'mst_i18n_ja' . fake()->uuid(),
            'client_mst_data_i18n_en_hash' => 'mst_i18n_en' . fake()->uuid(),
            'client_mst_data_i18n_zh_hash' => 'mst_i18n_zh' . fake()->uuid(),
            'client_opr_data_hash' => 'opr_' . fake()->uuid(),
            'client_opr_data_i18n_ja_hash' => 'opr_i18n_ja' . fake()->uuid(),
            'client_opr_data_i18n_en_hash' => 'opr_i18n_en' . fake()->uuid(),
            'client_opr_data_i18n_zh_hash' => 'opr_i18n_zh' . fake()->uuid(),
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
