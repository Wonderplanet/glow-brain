<?php

declare(strict_types=1);

namespace Filament\Resources\MngAssetReleaseResource\Pages;

use Livewire\Livewire;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource\Pages\ListMngAssetReleases;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmAssetImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetReleaseVersion;
use WonderPlanet\Tests\TestCase;

class ListMngAssetReleasesTest extends TestCase
{
    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        Livewire::test(ListMngAssetReleases::class)
            ->assertSuccessful();
    }

    /**
     * @test
     * @dataProvider canRenderTableData
     */
    public function canRender_テーブルにデータが表示されるか(string $tab, int $actual): void
    {
        // Setup
        $mngAssetReleases = [
            [
                // 配信終了
                'platform' => PlatformConstant::PLATFORM_IOS,
                'release_key' => 2024083101,
                'enabled' => 1,
                'target_release_version_id' => 'mngMasterReleaseVersion0',
                'client_compatibility_version' => '0.0.9',
            ],
            [
                // 配信中(最古)
                'platform' => PlatformConstant::PLATFORM_IOS,
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => 'mngMasterReleaseVersion1',
                'client_compatibility_version' => '1.0.0',
            ],
            [
                // 配信中(最新)
                'platform' => PlatformConstant::PLATFORM_IOS,
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => 'mngMasterReleaseVersion2',
                'client_compatibility_version' => '1.1.0',
            ],
            [
                // 配信準備中
                'platform' => PlatformConstant::PLATFORM_IOS,
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => null,
                'client_compatibility_version' => '1.2.0',
            ],
        ];
        $mngAssetReleaseVersions = [
            [
                'id' => 'mngAssetReleaseVersion0',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'release_key' => 2024083101,
                'git_revision' => 'git_revision_2024083101',
                'git_branch' => 'branch',
                'catalog_hash' => 'catalog_hash',
                'build_client_version' => '1.1.0',
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => 'catalog_file_name',
                'catalog_hash_file_name' => 'catalog_hash_file_name',
            ],
            [
                'id' => 'mngAssetReleaseVersion1',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'release_key' => 2024090101,
                'git_revision' => 'git_revision_2024090101',
                'git_branch' => 'branch',
                'catalog_hash' => 'catalog_hash',
                'build_client_version' => '1.1.1',
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => 'catalog_file_name',
                'catalog_hash_file_name' => 'catalog_hash_file_name',
            ],
            [
                'id' => 'mngMasterReleaseVersion2',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'release_key' => 2024090102,
                'git_revision' => 'git_revision_2024090102',
                'git_branch' => 'branch',
                'catalog_hash' => 'catalog_hash',
                'build_client_version' => '1.1.1',
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => 'catalog_file_name',
                'catalog_hash_file_name' => 'catalog_hash_file_name',
            ],
        ];
        $admAssetImportHistories = [
            [
                'id' => 'admAssetImportHistory0',
                'mng_asset_release_version_id' => 'mngAssetReleaseVersion0',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024083101',
                'created_at' => '2024-08-31 10:00:00',
                'updated_at' => '2024-08-31 10:00:00',
            ],
            [
                'id' => 'admAssetImportHistory1',
                'mng_asset_release_version_id' => 'mngAssetReleaseVersion1',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090101',
                'created_at' => '2024-09-01 10:00:00',
                'updated_at' => '2024-09-01 10:00:00',
            ],
            [
                'id' => 'admAssetImportHistory2',
                'mng_asset_release_version_id' => 'mngMasterReleaseVersion2',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090102',
                'created_at' => '2024-09-01 12:00:00',
                'updated_at' => '2024-09-01 12:00:00',
            ],
        ];
        foreach ($mngAssetReleases as $mngAssetRelease) {
            MngAssetRelease::query()
                ->create($mngAssetRelease);
        }
        foreach ($mngAssetReleaseVersions as $mngAssetReleaseVersion) {
            MngAssetReleaseVersion::query()
                ->create($mngAssetReleaseVersion);
        }
        foreach ($admAssetImportHistories as $admAssetImportHistory) {
            // idを指定したもので登録したいのでinsertで生成
            AdmAssetImportHistory::query()
                ->insert($admAssetImportHistory);
        }

        // Verify
        Livewire::test(ListMngAssetReleases::class, ['activeTab' => $tab])
            // 指定したカラムがテーブルに表示されている
            ->assertCanRenderTableColumn('platform')
            ->assertCanRenderTableColumn('release_key')
            ->assertCanRenderTableColumn('client_compatibility_version')
            ->assertCanRenderTableColumn('enabled')
            ->assertCanRenderTableColumn('mngAssetReleaseVersion.git_revision')
            ->assertCanRenderTableColumn('mngAssetReleaseVersion.catalog_hash')
            ->assertCanRenderTableColumn('mngAssetReleaseVersion.asset_total_byte_size')
            ->assertCanRenderTableColumn('custom_import_at')
            // タブがmainなら配信中/準備中のデータが3件表示される
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
