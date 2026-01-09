<?php

declare(strict_types=1);

namespace Filament\Resources\MngMasterReleaseResource\Pages;

use Livewire\Livewire;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Pages\ListMngMasterReleases;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Tests\TestCase;

class ListMngMasterReleasesTest extends TestCase
{
    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        Livewire::test(ListMngMasterReleases::class)
            ->assertSuccessful();
    }

    /**
     * @test
     * @dataProvider canRenderTableData
     */
    public function canRender_テーブルにデータが表示されるか(string $tab, int $actual): void
    {
        // Setup
        $mngMasterReleases = [
            [
                // 配信終了
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => 'mngMasterReleaseVersion1',
                'client_compatibility_version' => '1.0.0',
            ],
            [
                // 配信中
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => 'mngMasterReleaseVersion2',
                'client_compatibility_version' => '1.1.0',
            ],
            [
                // 配信中(最新)
                'release_key' => 2024090103,
                'enabled' => 1,
                'target_release_version_id' => 'mngMasterReleaseVersion3',
                'client_compatibility_version' => '1.1.1',
            ],
            [
                // 配信準備中
                'release_key' => 2024090104,
                'enabled' => 0,
                'target_release_version_id' => null,
                'client_compatibility_version' => '1.2.0',
            ],
        ];
        $mngMasterReleaseVersions = [
            [
                'id' => 'mngMasterReleaseVersion1',
                'release_key' => 2024090101,
                'git_revision' => 'git_revision_2024090101',
                'master_schema_version' => 'master_schema_version_2024090101',
                'data_hash' => 'data_hash_2024090101',
                'server_db_hash' => 'server_db_hash_2024090101',
                'client_mst_data_hash' => 'client_mst_data_hash_2024090101',
                'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_2024090101',
                'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_2024090101',
                'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_2024090101',
                'client_opr_data_hash' => 'client_opr_data_hash_2024090101',
                'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_2024090101',
                'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_2024090101',
                'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_2024090101',
            ],
            [
                'id' => 'mngMasterReleaseVersion2',
                'release_key' => 2024090102,
                'git_revision' => 'git_revision_2024090102',
                'master_schema_version' => 'master_schema_version_2024090102',
                'data_hash' => 'data_hash_2024090102',
                'server_db_hash' => 'server_db_hash_2024090102',
                'client_mst_data_hash' => 'client_mst_data_hash_2024090102',
                'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_2024090102',
                'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_2024090102',
                'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_2024090102',
                'client_opr_data_hash' => 'client_opr_data_hash_2024090102',
                'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_2024090102',
                'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_2024090102',
                'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_2024090102',
            ],
            [
                'id' => 'mngMasterReleaseVersion3',
                'release_key' => 2024090103,
                'git_revision' => 'git_revision_2024090103',
                'master_schema_version' => 'master_schema_version_2024090103',
                'data_hash' => 'data_hash_2024090103',
                'server_db_hash' => 'server_db_hash_2024090103',
                'client_mst_data_hash' => 'client_mst_data_hash_2024090103',
                'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_2024090103',
                'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_2024090103',
                'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_2024090103',
                'client_opr_data_hash' => 'client_opr_data_hash_2024090103',
                'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_2024090103',
                'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_2024090103',
                'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_2024090103',
            ],
        ];
        $admMasterImportHistories = [
            [
                'id' => 'admMasterImportHistory1',
                'git_revision' => 'git_revision_2024090101',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090101',
                'created_at' => '2024-09-01 10:00:00',
                'updated_at' => '2024-09-01 10:00:00',
            ],
            [
                'id' => 'admMasterImportHistory2',
                'git_revision' => 'git_revision_2024090102',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090102',
                'created_at' => '2024-09-01 12:00:00',
                'updated_at' => '2024-09-01 12:00:00',
            ],
            [
                'id' => 'admMasterImportHistory3',
                'git_revision' => 'git_revision_2024090103',
                'import_adm_user_id' => 'admin_3',
                'import_source' => 'import_source_2024090103',
                'created_at' => '2024-09-02 12:00:00',
                'updated_at' => '2024-09-02 12:00:00',
            ],
        ];
        $admMasterImportHistoryVersions = [
            [
                'adm_master_import_history_id' => 'admMasterImportHistory1',
                'mng_master_release_version_id' => 'mngMasterReleaseVersion1',
            ],
            [
                'adm_master_import_history_id' => 'admMasterImportHistory2',
                'mng_master_release_version_id' => 'mngMasterReleaseVersion2',
            ],
            [
                'adm_master_import_history_id' => 'admMasterImportHistory3',
                'mng_master_release_version_id' => 'mngMasterReleaseVersion3',
            ],
        ];
        MngMasterRelease::factory()->createMany($mngMasterReleases);
        MngMasterReleaseVersion::factory()->createMany($mngMasterReleaseVersions);
        AdmMasterImportHistory::factory()->createMany($admMasterImportHistories);
        AdmMasterImportHistoryVersion::factory()->createMany($admMasterImportHistoryVersions);

        // Verify
        Livewire::test(ListMngMasterReleases::class, ['activeTab' => $tab])
            // 指定したカラムがテーブルに表示されている
            ->assertCanRenderTableColumn('release_key')
            ->assertCanRenderTableColumn('client_compatibility_version')
            ->assertCanRenderTableColumn('enabled')
            ->assertCanRenderTableColumn('mngMasterReleaseVersion.git_revision')
            ->assertCanRenderTableColumn('mngMasterReleaseVersion.data_hash')
            ->assertCanRenderTableColumn('custom_import_at')
            ->assertCanRenderTableColumn('custom_icon')
            // タブがmainなら配信中/準備中のデータが2件表示される
            // タブがexpiredなら配信終了のデータが1件表示される
            ->assertCountTableRecords($actual);
    }

    /**
     * @return array[]
     */
    private function canRenderTableData(): array
    {
        return [
            'タブが配信中/準備中' => ['main', 3],
            'タブが配信終了' => ['expired', 1],
        ];
    }
}
