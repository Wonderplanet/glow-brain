<?php

declare(strict_types=1);

namespace Filament\Pages;

use Filament\Notifications\Notification;
use Livewire\Livewire;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases\MngMasterAndAssetReleaseUpdate;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmAssetImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Tests\TestCase;

class MngMasterAndAssetReleaseUpdateTest extends TestCase
{
    use ReflectionTrait;

    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                'id' => 'master-1',
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-2',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中 設定なし
                'id' => 'master-3',
                'release_key' => 2024090104,
                'enabled' => 0,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090102,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090103,
            ]);
        AdmMasterImportHistory::factory()
            ->create([
                'id' => 'history-1',
                'created_at' => '2024-10-29 12:00:00',
            ]);
        AdmMasterImportHistory::factory()
            ->create([
                'id' => 'history-2',
                'created_at' => '2024-10-29 12:30:00',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'adm_master_import_history_id' => 'history-1',
                'mng_master_release_version_id' => '101',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'adm_master_import_history_id' => 'history-2',
                'mng_master_release_version_id' => '102',
            ]);
        // アセットデータ(ios)
        MngAssetRelease::factory()
            ->create([
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090102,
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '101',
            ]);
        // アセットデータ(ios)
        MngAssetRelease::factory()
            ->create([
                'release_key' => 2024090102,
                'enabled' => 1,
                'platform' => 2,
                'target_release_version_id' => '102',
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090102,
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '102',
            ]);

        // Exercise
        Livewire::test(MngMasterAndAssetReleaseUpdate::class)
            ->assertSuccessful();
    }
    
    /**
     * @test
     */
    public function canRender_配信中のデータがない場合でもエラーにならないこと(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-2',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中 設定なし
                'id' => 'master-3',
                'release_key' => 2024090104,
                'enabled' => 0,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090103,
            ]);
        AdmMasterImportHistory::factory()
            ->create([
                'id' => 'history-2',
                'created_at' => '2024-10-29 12:30:00',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'adm_master_import_history_id' => 'history-2',
                'mng_master_release_version_id' => '102',
            ]);

        // Exercise
        Livewire::test(MngMasterAndAssetReleaseUpdate::class)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function getViewData_データチェック(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                // 配信中
                'id' => 'master-1',
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-2',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中 設定なし
                'id' => 'master-3',
                'release_key' => 2024090104,
                'enabled' => 0,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '101',
                'release_key' => 2024090102,
                'data_hash' => 'data_hash_1',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                // 配信準備中
                'id' => '102',
                'release_key' => 2024090103,
                'data_hash' => 'data_hash_2',
            ]);
        AdmMasterImportHistory::factory()
            ->create([
                'id' => 'history-1',
                'created_at' => '2024-10-29 12:00:00',
            ]);
        AdmMasterImportHistory::factory()
            ->create([
                'id' => 'history-2',
                'created_at' => '2024-10-29 12:30:00',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'adm_master_import_history_id' => 'history-1',
                'mng_master_release_version_id' => '101',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'adm_master_import_history_id' => 'history-2',
                'mng_master_release_version_id' => '102',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '102',
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '101',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => 'catalog_ios_1'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '102',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => 'catalog_android_1'
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '101',
                'created_at' => '2024-10-29 10:00:00',
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '102',
                'created_at' => '2024-10-29 11:00:00',
            ]);

        // Exercise
        $class = $this->app->make(
            MngMasterAndAssetReleaseUpdate::class,
        );
        $class->mount();
        $actual = $this->callMethod($class, 'getViewData');

        // Verify
        $currentDataMaster = $actual['currentData']['master'];
        $this->assertEquals(2024090102, $currentDataMaster[0]['releaseKey']);
        $this->assertEquals('data_hash_1', $currentDataMaster[0]['dataHash']);
        $this->assertEquals('2024/10/29 21:00:00', $currentDataMaster[0]['importedAt']);
        $currentDataAssetIos = $actual['currentData']['assetIos'];
        $this->assertEquals(2024090102, $currentDataAssetIos[0]['releaseKey']);
        $this->assertEquals('catalog_ios_1', $currentDataAssetIos[0]['dataHash']);
        $this->assertEquals('2024/10/29 19:00:00', $currentDataAssetIos[0]['importedAt']);
        $currentDataAssetAndroid = $actual['currentData']['assetAndroid'];
        $this->assertEquals(2024090102, $currentDataAssetAndroid[0]['releaseKey']);
        $this->assertEquals('catalog_android_1', $currentDataAssetAndroid[0]['dataHash']);
        $this->assertEquals('2024/10/29 20:00:00', $currentDataAssetAndroid[0]['importedAt']);
        $targetDataMaster = $actual['targetData']['master'];
        $this->assertEquals(2024090103, $targetDataMaster[0]['releaseKey']);
        $this->assertEquals('data_hash_2', $targetDataMaster[0]['dataHash']);
        $this->assertEquals('2024/10/29 21:30:00', $targetDataMaster[0]['importedAt']);
    }

    /**
     * @test
     */
    public function onCheckMngReleaseId_配信準備データをチェックした時の動作チェック(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                // 配信中
                'id' => 'master-1',
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        $pendingMasterRelease = MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-2',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中 設定なし
                'id' => 'master-3',
                'release_key' => 2024090104,
                'enabled' => 0,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090102,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090103,
                'data_hash' => 'data_hash_1'
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中
                'id' => 'asset-ios-1',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中
                'id' => 'asset-android-1',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '102',
            ]);
        $pendingAssetReleaseIos = MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'asset-ios-2',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
                'target_release_version_id' => '103',
            ]);
        $pendingAssetReleaseAndroid = MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'asset-android-2',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
                'target_release_version_id' => '104',
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '101',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => 'catalog_ios_1'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '102',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => 'catalog_android_1'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信準備中
                'id' => '103',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => 'catalog_ios_2'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信準備中
                'id' => '104',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => 'catalog_android_2'
            ]);

        // Exercise
        $class = $this->app->make(
            MngMasterAndAssetReleaseUpdate::class,
        );
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_MASTER, $pendingMasterRelease->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_IOS, $pendingAssetReleaseIos->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_ANDROID, $pendingAssetReleaseAndroid->id);

        // Verify
        // チェックした配信準備中のidが保持されているか
        $this->assertEquals(['master-2'], $class->checkMasterReleaseIds);
        $this->assertEquals(['asset-ios-2'], $class->checkAssetIosReleaseIds);
        $this->assertEquals(['asset-android-2'], $class->checkAssetAndroidReleaseIds);
    }

    /**
     * @test
     */
    public function onCheckMngReleaseId_チェックを外した時の動作チェック(): void
    {
        // Setup
        $pendingMasterRelease1 = MngMasterRelease::factory()
            ->create([
                // 配信準備中1
                'id' => 'master-1',
                'release_key' => 2024090102,
                'enabled' => 0,
                'target_release_version_id' => '101',
            ]);
        $pendingMasterRelease2 = MngMasterRelease::factory()
            ->create([
                // 配信準備中2
                'id' => 'master-2',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090102,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090103,
            ]);
        $pendingAssetReleaseIos1 = MngAssetRelease::factory()
            ->create([
                // 配信準備中1
                'id' => 'asset-ios-1',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
                'target_release_version_id' => '101',
            ]);
        $pendingAssetReleaseAndroid1 = MngAssetRelease::factory()
            ->create([
                // 配信準備中1
                'id' => 'asset-android-1',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ]);
        $pendingAssetReleaseIos2 = MngAssetRelease::factory()
            ->create([
                // 配信準備中2
                'id' => 'asset-ios-2',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
                'target_release_version_id' => '103',
            ]);
        $pendingAssetReleaseAndroid2 = MngAssetRelease::factory()
            ->create([
                // 配信準備中2
                'id' => 'asset-android-2',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
                'target_release_version_id' => '104',
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信準備中1
                'id' => '101',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => 'catalog_ios_1'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信準備中1
                'id' => '102',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => 'catalog_android_1'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信準備中2
                'id' => '103',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => 'catalog_ios_2'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信準備中2
                'id' => '104',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => 'catalog_android_2'
            ]);
        // チェック済みにする
        $class = $this->app->make(
            MngMasterAndAssetReleaseUpdate::class,
        );
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_MASTER, $pendingMasterRelease1->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_MASTER, $pendingMasterRelease2->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_IOS, $pendingAssetReleaseIos1->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_IOS, $pendingAssetReleaseIos2->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_ANDROID, $pendingAssetReleaseAndroid1->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_ANDROID, $pendingAssetReleaseAndroid2->id);

        // Exercise
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_MASTER, $pendingMasterRelease2->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_IOS, $pendingAssetReleaseIos2->id);
        $class->onCheckMngReleaseId(MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_ANDROID, $pendingAssetReleaseAndroid2->id);

        // Verify
        // チェックを外したした配信準備中のidが除外されているか
        $this->assertEquals([$pendingMasterRelease1->id], $class->checkMasterReleaseIds);
        $this->assertEquals([$pendingAssetReleaseIos1->id], $class->checkAssetIosReleaseIds);
        $this->assertEquals([$pendingAssetReleaseAndroid1->id], $class->checkAssetAndroidReleaseIds);
    }
    
    /**
     * @test
     * @dataProvider onCheckMngReleaseIdNoticeData
     */
    public function onCheckMngReleaseId_3つ以上チェックした時の動作チェック(string $dataType, string $title): void
    {
        // Setup
        // チェック済みにする
        $class = $this->app->make(
            MngMasterAndAssetReleaseUpdate::class,
        );
        $class->onCheckMngReleaseId($dataType, '100');
        $class->onCheckMngReleaseId($dataType, '101');

        // Exercise
        $class->onCheckMngReleaseId($dataType, '103');

        // Verify
        // 想定したエラー通知かチェック
        Notification::assertNotified(
            Notification::make()
                ->title($title)
                ->color('danger')
                ->danger()
                ->persistent()
                ->send()
        );
    }

    /**
     * @return array
     */
    private function onCheckMngReleaseIdNoticeData(): array
    {
        return [
            'マスター' => [MngMasterAndAssetReleaseUpdate::DATA_TYPE_MASTER, 'マスターリリースキーの切り替えは2件以上行えません'],
            'アセット(ios)' => [MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_IOS, 'アセットリリースキー(ios)の切り替えは2件以上行えません'],
            'アセット(android)' => [MngMasterAndAssetReleaseUpdate::DATA_TYPE_ASSET_ANDROID, 'アセットリリースキー(android)の切り替えは2件以上行えません'],
        ];
    }

    /**
     * @test
     */
    public function makeConfirmModalDataByMasterRelease_表示データ生成チェック_配信と終了が2つずつある(): void
    {
        // Setup
        MngMasterRelease::factory()->createMany([
            [
                // 配信終了
                'id' => 'master-0',
                'release_key' => 202408310,
                'enabled' => 1,
                'target_release_version_id' => '100',
                'client_compatibility_version' => '0.0.9',
                'description' => '配信終了',
            ],
            [
                // 配信中(最古)
                'id' => 'master-1',
                'release_key' => 202409010,
                'enabled' => 1,
                'target_release_version_id' => '101',
                'client_compatibility_version' => '1.0.0',
                'description' => '配信中(最古)',
            ],
            [
                // 配信中(最新)
                'id' => 'master-2',
                'release_key' => 202409020,
                'enabled' => 1,
                'target_release_version_id' => '102',
                'client_compatibility_version' => '1.1.0',
                'description' => '配信中(最新)',
            ],
            [
                // 配信準備中1
                'id' => 'master-3',
                'release_key' => 202409030,
                'enabled' => 0,
                'target_release_version_id' => '103',
                'client_compatibility_version' => '1.2.0',
                'description' => '配信準備中1',
            ],
            [
                // 配信準備中2
                'id' => 'master-4',
                'release_key' => 202409040,
                'enabled' => 0,
                'target_release_version_id' => '104',
                'client_compatibility_version' => '1.3.0',
                'description' => '配信準備中2',
            ],
            [
                // 配信準備中 設定なし
                'id' => 'master-5',
                'release_key' => 202409050,
                'enabled' => 0,
                'client_compatibility_version' => '1.4.0',
                'description' => '配信準備中',
            ],
        ]);
        MngMasterReleaseVersion::factory()->createMany([
            [
                'id' => '100',
                'release_key' => 202408310,
                'data_hash' => 'master-0-hash',
            ],
            [
                'id' => '101',
                'release_key' => 202409010,
                'data_hash' => 'master-1-hash',
            ],
            [
                'id' => '102',
                'release_key' => 202409020,
                'data_hash' => 'master-2-hash',
            ],
            [
                'id' => '103',
                'release_key' => 202409030,
                'data_hash' => 'master-3-hash',
            ],
            [
                'id' => '104',
                'release_key' => 202409040,
                'data_hash' => 'master-4-hash',
            ]
        ]);

        // Exercise
        $class = new MngMasterAndAssetReleaseUpdate;
        $class->mount();
        $class->checkMasterReleaseIds = ['master-3', 'master-4'];
        [$actualApplyReleases, $actualExpiredReleases] = $this->callMethod($class, 'makeConfirmModalDataByMasterRelease');

        // Verify
        // 配信中想定のデータが想定通りか
        $this->assertEquals(2, count($actualApplyReleases));
        $this->assertEquals([
            1 => [
                'releaseKey' => 202409040,
                'client_version' => '1.3.0',
                'description' => '配信準備中2',
                'dataHash' => 'master-4-hash',
            ],
            2 => [
                'releaseKey' => 202409030,
                'client_version' => '1.2.0',
                'description' => '配信準備中1',
                'dataHash' => 'master-3-hash',
            ],
        ], $actualApplyReleases);

        // 配信終了データが想定通りか
        $this->assertEquals(2, count($actualExpiredReleases));
        $this->assertEquals([
            3 => [
                'releaseKey' => 202409020,
                'client_version' => '1.1.0',
                'description' => '配信中(最新)',
                'dataHash' => 'master-2-hash',
            ],
            4 => [
                'releaseKey' => 202409010,
                'client_version' => '1.0.0',
                'description' => '配信中(最古)',
                'dataHash' => 'master-1-hash',
            ],
        ], $actualExpiredReleases);
    }

    /**
     * @test
     */
    public function makeConfirmModalDataByMasterRelease_表示データ生成チェック_終了なし(): void
    {
        // Setup
        MngMasterRelease::factory()->createMany([
            [
                // 配信中
                'id' => 'master-1',
                'release_key' => 202409010,
                'enabled' => 1,
                'target_release_version_id' => '101',
                'client_compatibility_version' => '1.0.0',
                'description' => '配信中',
            ],
            [
                // 配信準備中1
                'id' => 'master-2',
                'release_key' => 202409020,
                'enabled' => 0,
                'target_release_version_id' => '102',
                'client_compatibility_version' => '1.1.0',
                'description' => '配信準備中1',
            ],
            [
                // 配信準備中2
                'id' => 'master-3',
                'release_key' => 202409030,
                'enabled' => 0,
                'target_release_version_id' => '103',
                'client_compatibility_version' => '1.2.0',
                'description' => '配信準備中2',
            ],
            [
                // 配信準備中 設定なし
                'id' => 'master-4',
                'release_key' => 202409040,
                'enabled' => 0,
                'client_compatibility_version' => '1.3.0',
                'description' => '配信準備中2',
            ],
        ]);
        MngMasterReleaseVersion::factory()->createMany([
            [
                'id' => '101',
                'release_key' => 202409010,
                'data_hash' => 'master-1-hash',
            ],
            [
                'id' => '102',
                'release_key' => 202409020,
                'data_hash' => 'master-2-hash',
            ],
            [
                'id' => '103',
                'release_key' => 202409030,
                'data_hash' => 'master-3-hash',
            ],
        ]);

        // Exercise
        $class = new MngMasterAndAssetReleaseUpdate;
        $class->mount();
        $class->checkMasterReleaseIds = ['master-2'];
        [$actualApplyReleases, $actualExpiredReleases] = $this->callMethod($class, 'makeConfirmModalDataByMasterRelease');

        // Verify
        // 配信中想定のデータが想定通りか
        $this->assertEquals(2, count($actualApplyReleases));
        $this->assertEquals([
            2 => [
                'releaseKey' => 202409020,
                'client_version' => '1.1.0',
                'description' => '配信準備中1',
                'dataHash' => 'master-2-hash',
            ],
            3 => [
                'releaseKey' => 202409010,
                'client_version' => '1.0.0',
                'description' => '配信中',
                'dataHash' => 'master-1-hash',
            ],
        ], $actualApplyReleases);

        // 配信終了データが想定通りか
        $this->assertEquals(0, count($actualExpiredReleases));
    }

    /**
     * @test
     */
    public function makeConfirmModalDataByAssetRelease_表示データ生成チェック_ios_配信と終了が2つずつある(): void
    {
        // Setup
        MngAssetRelease::factory()->createMany([
            [
                // ios 配信終了
                'id' => 'asset-0-ios',
                'release_key' => 202408310,
                'platform' => 1,
                'enabled' => 1,
                'target_release_version_id' => '100-ios',
                'client_compatibility_version' => '0.0.9',
                'description' => '配信終了(ios)',
            ],
            [
                // ios 配信中(最古)
                'id' => 'asset-1-ios',
                'release_key' => 202409010,
                'platform' => 1,
                'enabled' => 1,
                'target_release_version_id' => '101-ios',
                'client_compatibility_version' => '1.0.0',
                'description' => '配信中 ios(最古)',
            ],
            [
                // ios 配信中(最新)
                'id' => 'asset-2-ios',
                'release_key' => 202409020,
                'platform' => 1,
                'enabled' => 1,
                'target_release_version_id' => '102-ios',
                'client_compatibility_version' => '1.1.0',
                'description' => '配信中 ios(最新)',
            ],
            [
                // ios 配信準備中1
                'id' => 'asset-3-ios',
                'release_key' => 202409030,
                'platform' => 1,
                'enabled' => 0,
                'target_release_version_id' => '103-ios',
                'client_compatibility_version' => '1.2.0',
                'description' => '配信準備中1 ios',
            ],
            [
                // 配信準備中2 ios
                'id' => 'asset-4-ios',
                'release_key' => 202409040,
                'platform' => 1,
                'enabled' => 0,
                'target_release_version_id' => '104-ios',
                'client_compatibility_version' => '1.3.0',
                'description' => '配信準備中2 ios',
            ],
            [
                // 配信準備中 ios 設定なし
                'id' => 'master-5-ios',
                'release_key' => 202409050,
                'platform' => 1,
                'enabled' => 0,
                'client_compatibility_version' => '1.4.0',
                'description' => '配信準備中 ios',
            ],
        ]);
        MngAssetReleaseVersion::factory()->createMany([
            [
                'id' => '100-ios',
                'release_key' => 202408310,
                'catalog_hash' => 'asset-0-ios-hash',
            ],
            [
                'id' => '101-ios',
                'release_key' => 202409010,
                'catalog_hash' => 'asset-1-ios-hash',
            ],
            [
                'id' => '102-ios',
                'release_key' => 202409020,
                'catalog_hash' => 'asset-2-ios-hash',
            ],
            [
                'id' => '103-ios',
                'release_key' => 202409030,
                'catalog_hash' => 'asset-3-ios-hash',
            ],
            [
                'id' => '104-ios',
                'release_key' => 202409040,
                'catalog_hash' => 'asset-4-ios-hash',
            ],
        ]);

        // Exercise
        $class = new MngMasterAndAssetReleaseUpdate;
        $class->mount();
        $class->checkAssetIosReleaseIds = ['asset-3-ios', 'asset-4-ios'];

        [$actualApplyReleases, $actualExpiredReleases] = $this->callMethod(
            $class,
            'makeConfirmModalDataByAssetRelease',
            $class->mngAssetReleasesIosByApply,
            $class->checkAssetIosReleaseIds,
            $class->mngAssetReleasesByIos
        );

        // Verify
        // 配信中想定のデータが想定通りか
        $this->assertEquals(2, count($actualApplyReleases));
        $this->assertEquals([
            1 => [
                'releaseKey' => 202409040,
                'client_version' => '1.3.0',
                'description' => '配信準備中2 ios',
                'dataHash' => 'asset-4-ios-hash',
            ],
            2 => [
                'releaseKey' => 202409030,
                'client_version' => '1.2.0',
                'description' => '配信準備中1 ios',
                'dataHash' => 'asset-3-ios-hash',
            ],
        ], $actualApplyReleases);

        // 配信終了データが想定通りか
        $this->assertEquals(2, count($actualExpiredReleases));
        $this->assertEquals([
            3 => [
                'releaseKey' => 202409020,
                'client_version' => '1.1.0',
                'description' => '配信中 ios(最新)',
                'dataHash' => 'asset-2-ios-hash',
            ],
            4 => [
                'releaseKey' => 202409010,
                'client_version' => '1.0.0',
                'description' => '配信中 ios(最古)',
                'dataHash' => 'asset-1-ios-hash',
            ],
        ], $actualExpiredReleases);
    }
    
    /**
     * @test
     */
    public function makeConfirmModalDataByAssetRelease_表示データ生成チェック_ios_終了なし(): void
    {
        // Setup
        MngAssetRelease::factory()->createMany([
            [
                // ios 配信中
                'id' => 'asset-1-ios',
                'release_key' => 202409010,
                'platform' => 1,
                'enabled' => 1,
                'target_release_version_id' => '101-ios',
                'client_compatibility_version' => '1.0.0',
                'description' => '配信中 ios',
            ],
            [
                // ios 配信準備中1
                'id' => 'asset-2-ios',
                'release_key' => 202409020,
                'platform' => 1,
                'enabled' => 0,
                'target_release_version_id' => '102-ios',
                'client_compatibility_version' => '1.1.0',
                'description' => '配信準備中1 ios',
            ],
            [
                // ios 配信準備中2
                'id' => 'asset-3-ios',
                'release_key' => 202409030,
                'platform' => 1,
                'enabled' => 0,
                'target_release_version_id' => '103-ios',
                'client_compatibility_version' => '1.2.0',
                'description' => '配信準備中2 ios',
            ],
            [
                // 配信準備中 ios 設定なし
                'id' => 'asset-4-ios',
                'release_key' => 202409040,
                'platform' => 1,
                'enabled' => 0,
                'client_compatibility_version' => '1.3.0',
                'description' => '配信準備中 ios',
            ],
        ]);
        MngAssetReleaseVersion::factory()->createMany([
            [
                'id' => '101-ios',
                'release_key' => 202409010,
                'catalog_hash' => 'asset-1-ios-hash',
            ],
            [
                'id' => '102-ios',
                'release_key' => 202409020,
                'catalog_hash' => 'asset-2-ios-hash',
            ],
            [
                'id' => '103-ios',
                'release_key' => 202409030,
                'catalog_hash' => 'asset-3-ios-hash',
            ],
        ]);

        // Exercise
        $class = new MngMasterAndAssetReleaseUpdate;
        $class->mount();
        $class->checkAssetIosReleaseIds = ['asset-2-ios'];

        [$actualApplyReleases, $actualExpiredReleases] = $this->callMethod(
            $class,
            'makeConfirmModalDataByAssetRelease',
            $class->mngAssetReleasesIosByApply,
            $class->checkAssetIosReleaseIds,
            $class->mngAssetReleasesByIos
        );

        // Verify
        // 配信中想定のデータが想定通りか
        $this->assertEquals(2, count($actualApplyReleases));
        $this->assertEquals([
            2 => [
                'releaseKey' => 202409020,
                'client_version' => '1.1.0',
                'description' => '配信準備中1 ios',
                'dataHash' => 'asset-2-ios-hash',
            ],
            3 => [
                'releaseKey' => 202409010,
                'client_version' => '1.0.0',
                'description' => '配信中 ios',
                'dataHash' => 'asset-1-ios-hash',
            ],
        ], $actualApplyReleases);

        // 配信終了データが想定通りか
        $this->assertEquals(0, count($actualExpiredReleases));
    }

    /**
     * @test
     */
    public function makeConfirmModalDataByAssetRelease_表示データ生成チェック_android_配信と終了が2つずつある(): void
    {
        // Setup
        MngAssetRelease::factory()->createMany([
            [
                // android 配信終了
                'id' => 'asset-0-android',
                'release_key' => 202408310,
                'platform' => 2,
                'enabled' => 1,
                'target_release_version_id' => '100-android',
                'client_compatibility_version' => '0.0.9',
                'description' => '配信終了(android)',
            ],
            [
                // android 配信中(最古)
                'id' => 'asset-1-android',
                'release_key' => 202409010,
                'platform' => 2,
                'enabled' => 1,
                'target_release_version_id' => '101-android',
                'client_compatibility_version' => '1.0.0',
                'description' => '配信中 android(最古)',
            ],
            [
                // android 配信中(最新)
                'id' => 'asset-2-android',
                'release_key' => 202409020,
                'platform' => 2,
                'enabled' => 1,
                'target_release_version_id' => '102-android',
                'client_compatibility_version' => '1.1.0',
                'description' => '配信中 android(最新)',
            ],
            [
                // android 配信準備中1
                'id' => 'asset-3-android',
                'release_key' => 202409030,
                'platform' => 2,
                'enabled' => 0,
                'target_release_version_id' => '103-android',
                'client_compatibility_version' => '1.2.0',
                'description' => '配信準備中1 android',
            ],
            [
                // 配信準備中2 android
                'id' => 'asset-4-android',
                'release_key' => 202409040,
                'platform' => 2,
                'enabled' => 0,
                'target_release_version_id' => '104-android',
                'client_compatibility_version' => '1.3.0',
                'description' => '配信準備中2 android',
            ],
            [
                // 配信準備中 android 設定なし
                'id' => 'asset-5-android',
                'release_key' => 202409050,
                'platform' => 2,
                'enabled' => 0,
                'client_compatibility_version' => '1.4.0',
                'description' => '配信準備中 android',
            ],
        ]);
        MngAssetReleaseVersion::factory()->createMany([
            [
                'id' => '100-android',
                'release_key' => 202408310,
                'catalog_hash' => 'asset-0-android-hash',
            ],
            [
                'id' => '101-android',
                'release_key' => 202409010,
                'catalog_hash' => 'asset-1-android-hash',
            ],
            [
                'id' => '102-android',
                'release_key' => 202409020,
                'catalog_hash' => 'asset-2-android-hash',
            ],
            [
                'id' => '103-android',
                'release_key' => 202409030,
                'catalog_hash' => 'asset-3-android-hash',
            ],
            [
                'id' => '104-android',
                'release_key' => 202409040,
                'catalog_hash' => 'asset-4-android-hash',
            ]
        ]);

        // Exercise
        $class = new MngMasterAndAssetReleaseUpdate;
        $class->mount();
        $class->checkAssetAndroidReleaseIds = ['asset-3-android', 'asset-4-android'];

        [$actualApplyReleases, $actualExpiredReleases] = $this->callMethod(
            $class,
            'makeConfirmModalDataByAssetRelease',
            $class->mngAssetReleasesAndroidByApply,
            $class->checkAssetAndroidReleaseIds,
            $class->mngAssetReleasesByAndroid
        );

        // Verify
        // 配信中想定のデータが想定通りか
        $this->assertEquals(2, count($actualApplyReleases));
        $this->assertEquals([
            1 => [
                'releaseKey' => 202409040,
                'client_version' => '1.3.0',
                'description' => '配信準備中2 android',
                'dataHash' => 'asset-4-android-hash',
            ],
            2 => [
                'releaseKey' => 202409030,
                'client_version' => '1.2.0',
                'description' => '配信準備中1 android',
                'dataHash' => 'asset-3-android-hash',
            ],
        ], $actualApplyReleases);
        
        // 配信終了データが想定通りか
        $this->assertEquals(2, count($actualExpiredReleases));
        $this->assertEquals([
            3 => [
                'releaseKey' => 202409020,
                'client_version' => '1.1.0',
                'description' => '配信中 android(最新)',
                'dataHash' => 'asset-2-android-hash',
            ],
            4 => [
                'releaseKey' => 202409010,
                'client_version' => '1.0.0',
                'description' => '配信中 android(最古)',
                'dataHash' => 'asset-1-android-hash',
            ],
        ], $actualExpiredReleases);
    }

    /**
     * @test
     */
    public function makeConfirmModalDataByAssetRelease_表示データ生成チェック_android_終了なし(): void
    {
        // Setup
        MngAssetRelease::factory()->createMany([
            [
                // android 配信中
                'id' => 'asset-1-android',
                'release_key' => 202409010,
                'platform' => 2,
                'enabled' => 1,
                'target_release_version_id' => '101-android',
                'client_compatibility_version' => '1.0.0',
                'description' => '配信中 android',
            ],
            [
                // android 配信準備中1
                'id' => 'asset-2-android',
                'release_key' => 202409020,
                'platform' => 2,
                'enabled' => 0,
                'target_release_version_id' => '102-android',
                'client_compatibility_version' => '1.1.0',
                'description' => '配信準備中1 android',
            ],
            [
                // android 配信準備中2
                'id' => 'asset-3-android',
                'release_key' => 202409030,
                'platform' => 2,
                'enabled' => 0,
                'target_release_version_id' => '103-android',
                'client_compatibility_version' => '1.2.0',
                'description' => '配信準備中2 android',
            ],
            [
                // 配信準備中 android 設定なし
                'id' => 'asset-4-android',
                'release_key' => 202409040,
                'platform' => 2,
                'enabled' => 0,
                'client_compatibility_version' => '1.3.0',
                'description' => '配信準備中 android',
            ],
        ]);
        MngAssetReleaseVersion::factory()->createMany([
            [
                'id' => '101-android',
                'release_key' => 202409010,
                'catalog_hash' => 'asset-1-android-hash',
            ],
            [
                'id' => '102-android',
                'release_key' => 202409020,
                'catalog_hash' => 'asset-2-android-hash',
            ],
            [
                'id' => '103-android',
                'release_key' => 202409030,
                'catalog_hash' => 'asset-3-android-hash',
            ],
        ]);

        // Exercise
        $class = new MngMasterAndAssetReleaseUpdate;
        $class->mount();
        $class->checkAssetAndroidReleaseIds = ['asset-2-android'];

        [$actualApplyReleases, $actualExpiredReleases] = $this->callMethod(
            $class,
            'makeConfirmModalDataByAssetRelease',
            $class->mngAssetReleasesAndroidByApply,
            $class->checkAssetAndroidReleaseIds,
            $class->mngAssetReleasesByAndroid
        );

        // Verify
        // 配信中想定のデータが想定通りか
        $this->assertEquals(2, count($actualApplyReleases));
        $this->assertEquals([
            2 => [
                'releaseKey' => 202409020,
                'client_version' => '1.1.0',
                'description' => '配信準備中1 android',
                'dataHash' => 'asset-2-android-hash',
            ],
            3 => [
                'releaseKey' => 202409010,
                'client_version' => '1.0.0',
                'description' => '配信中 android',
                'dataHash' => 'asset-1-android-hash',
            ],
        ], $actualApplyReleases);

        // 配信終了データが想定通りか
        $this->assertEquals(0, count($actualExpiredReleases));
    }

    /**
     * @test
     */
    public function execute_実行チェック(): void
    {
        // Setup
        // マスターデータ
        MngMasterRelease::factory()
            ->create([
                // 配信中
                'id' => 'master-1',
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        $checkMngMasterRelease = MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-2',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中 設定なし
                'id' => 'master-3',
                'release_key' => 2024090104,
                'enabled' => 0,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090102,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090103,
                'data_hash' => 'data_hash_1'
            ]);
        AdmMasterImportHistory::factory()
            ->create([
                'id' => 'history-1',
                'created_at' => '2024-10-29 12:00:00',
            ]);
        AdmMasterImportHistory::factory()
            ->create([
                'id' => 'history-2',
                'created_at' => '2024-10-29 12:30:00',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'adm_master_import_history_id' => 'history-1',
                'mng_master_release_version_id' => '101',
            ]);
        AdmMasterImportHistoryVersion::factory()
            ->create([
                'adm_master_import_history_id' => 'history-2',
                'mng_master_release_version_id' => '102',
            ]);
        // アセットデータ
        MngAssetRelease::factory()
            ->create([
                // 配信中
                'id' => 'asset-ios-1',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中
                'id' => 'asset-android-1',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '102',
            ]);
        $checkMngAssetReleaseIos = MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'asset-ios-2',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
                'target_release_version_id' => '103',
            ]);
        $checkMngAssetReleaseAndroid = MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'asset-android-2',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
                'target_release_version_id' => '104',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'asset-ios-3',
                'release_key' => 2024090104,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'asset-android-3',
                'release_key' => 2024090104,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '101',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => 'catalog_ios_1'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '102',
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => 'catalog_android_1'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '103',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => 'catalog_ios_2'
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                // 配信中
                'id' => '104',
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => 'catalog_android_2'
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '101',
                'created_at' => '2024-10-29 10:00:00',
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '102',
                'created_at' => '2024-10-29 11:00:00',
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '103',
                'created_at' => '2024-10-29 10:30:00',
            ]);
        AdmAssetImportHistory::factory()
            ->create([
                'mng_asset_release_version_id' => '104',
                'created_at' => '2024-10-29 11:30:00',
            ]);

        // Exercise
        $mngMasterAndAssetReleaseUpdate = new MngMasterAndAssetReleaseUpdate();
        $mngMasterAndAssetReleaseUpdate->mount();
        $mngMasterAndAssetReleaseUpdate->checkMasterReleaseIds = [$checkMngMasterRelease->id];
        $mngMasterAndAssetReleaseUpdate->checkAssetIosReleaseIds = [$checkMngAssetReleaseIos->id];
        $mngMasterAndAssetReleaseUpdate->checkAssetAndroidReleaseIds = [$checkMngAssetReleaseAndroid->id];
        $this->callMethod(
            $mngMasterAndAssetReleaseUpdate,
            'execute'
        );

        // Verify
        // マスターデータのenabledをチェック
        $mngMasterReleases = MngMasterRelease::all();
        $mngMasterRelease1 = $mngMasterReleases->first(fn ($row) => $row->id === 'master-1');
        $this->assertTrue((bool) $mngMasterRelease1->enabled);
        $mngMasterRelease2 = $mngMasterReleases->first(fn ($row) => $row->id === 'master-2');
        $this->assertTrue((bool) $mngMasterRelease2->enabled);
        $mngMasterRelease3 = $mngMasterReleases->first(fn ($row) => $row->id === 'master-3');
        $this->assertFalse((bool) $mngMasterRelease3->enabled);

        // アセットデータのenabledをチェック
        $mngAssetReleases = MngAssetRelease::all();
        $mngAssetReleaseIos1 = $mngAssetReleases->first(fn ($row) => $row->id === 'asset-ios-1');
        $this->assertTrue((bool) $mngAssetReleaseIos1->enabled);
        $mngAssetReleaseAndroid1 = $mngAssetReleases->first(fn ($row) => $row->id === 'asset-android-1');
        $this->assertTrue((bool) $mngAssetReleaseAndroid1->enabled);
        $mngAssetReleaseIos2 = $mngAssetReleases->first(fn ($row) => $row->id === 'asset-ios-2');
        $this->assertTrue((bool) $mngAssetReleaseIos2->enabled);
        $mngAssetReleaseAndroid2 = $mngAssetReleases->first(fn ($row) => $row->id === 'asset-android-2');
        $this->assertTrue((bool) $mngAssetReleaseAndroid2->enabled);
        $mngAssetReleaseIos3 = $mngAssetReleases->first(fn ($row) => $row->id === 'asset-ios-3');
        $this->assertFalse((bool) $mngAssetReleaseIos3->enabled);
        $mngAssetReleaseAndroid3 = $mngAssetReleases->first(fn ($row) => $row->id === 'asset-android-3');
        $this->assertFalse((bool) $mngAssetReleaseAndroid3->enabled);

        // 更新成功の通知が表示されるか
        Notification::assertNotified(
            Notification::make()
                ->title('リリース更新が完了しました')
                ->success()
        );
    }

    /**
     * @test
     */
    public function execute_実行時にエラーが発生(): void
    {
        // 存在しないマスターデータを更新しようとしてエラーが発生したと仮定

        // Setup
        // マスターデータ
        $id = 'master-1';
        MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => $id,
                'release_key' => 2024090101,
                'enabled' => 0,
                'target_release_version_id' => '101',
            ]);

        // Exercise
        $mngMasterAndAssetReleaseUpdate = new MngMasterAndAssetReleaseUpdate();
        $mngMasterAndAssetReleaseUpdate->mount();
        $mngMasterAndAssetReleaseUpdate->checkMasterReleaseIds = [$id, '999'];
        $this->callMethod(
            $mngMasterAndAssetReleaseUpdate,
            'execute'
        );

        // Verify
        // 更新失敗の通知が表示されるか
        Notification::assertNotified(
            Notification::make()
                ->title('リリース更新に失敗しました')
                ->body('サーバー管理者にお問い合わせください。')
                ->danger()
                ->color('danger')
        );
        // MngMasterReleaseがロールバックされているか
        $actual = MngMasterRelease::query()
            ->where('id', $id)
            ->first();
        $this->assertFalse((bool) $actual->enabled);
    }
}
