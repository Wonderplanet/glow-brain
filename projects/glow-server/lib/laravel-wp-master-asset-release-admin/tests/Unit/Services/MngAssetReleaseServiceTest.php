<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Unit\Services;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums\ReleaseStatus;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmAssetImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;
use WonderPlanet\Tests\TestCase;

class MngAssetReleaseServiceTest extends TestCase
{
    use ReflectionTrait;

    private MngAssetReleaseService $mngAssetReleaseService;

    public function setUp(): void
    {
        parent::setUp();

        $this->mngAssetReleaseService = app(MngAssetReleaseService::class);
    }
    
    /**
     * @test
     */
    public function getAllPlatformLatestReleasedMngAssetReleases_データ取得チェック(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->create([
                // 配信終了
                'release_key' => 202408310,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '0-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信終了
                'release_key' => 202408310,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '0-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最古)
                'release_key' => 2024090101,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '100-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最古)
                'release_key' => 2024090101,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '100-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最新)
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '101-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最新)
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '101-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
            ]);
        
        // Exercise
        $actuals = $this->mngAssetReleaseService->getAllPlatformLatestReleasedMngAssetReleases();
        
        // Verify
        $this->assertCount(2, $actuals);
        $actualByIos2 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_IOS);
        $this->assertEquals(2024090102, $actualByIos2->release_key);
        $this->assertTrue((bool) $actualByIos2->enabled);
        $this->assertEquals('101-ios', $actualByIos2->target_release_version_id);
        $actualByAndroid2 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_ANDROID);
        $this->assertEquals(2024090102, $actualByAndroid2->release_key);
        $this->assertTrue((bool) $actualByAndroid2->enabled);
        $this->assertEquals('101-android', $actualByAndroid2->target_release_version_id);
    }

    /**
     * @test
     */
    public function getAllPlatformApplyMngAssetReleases_データ取得チェック(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->create([
                // 配信終了
                'release_key' => 202408310,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '0-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信終了
                'release_key' => 202408310,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '0-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最古)
                'release_key' => 2024090101,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '100-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最古)
                'release_key' => 2024090101,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '100-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最新)
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '101-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最新)
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '101-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
            ]);

        // Exercise
        $actuals = $this->mngAssetReleaseService->getAllPlatformApplyMngAssetReleases();

        // Verify
        $this->assertCount(4, $actuals);
        $actualByIos1 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_IOS && $row->release_key === 2024090101);
        $this->assertEquals(2024090101, $actualByIos1->release_key);
        $this->assertTrue((bool) $actualByIos1->enabled);
        $this->assertEquals('100-ios', $actualByIos1->target_release_version_id);
        $actualByIos2 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_IOS && $row->release_key === 2024090102);
        $this->assertEquals(2024090102, $actualByIos2->release_key);
        $this->assertTrue((bool) $actualByIos2->enabled);
        $this->assertEquals('101-ios', $actualByIos2->target_release_version_id);

        $actualByAndroid1 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_ANDROID && $row->release_key === 2024090101);
        $this->assertEquals(2024090101, $actualByAndroid1->release_key);
        $this->assertTrue((bool) $actualByAndroid1->enabled);
        $this->assertEquals('100-android', $actualByAndroid1->target_release_version_id);
        $actualByAndroid2 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_ANDROID && $row->release_key === 2024090102);
        $this->assertEquals(2024090102, $actualByAndroid2->release_key);
        $this->assertTrue((bool) $actualByAndroid2->enabled);
        $this->assertEquals('101-android', $actualByAndroid2->target_release_version_id);
    }

    /**
     * @test
     */
    public function getMngAssetReleasesByApplyOrPending_データ取得チェック(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->create([
                // 配信終了
                'release_key' => 2024090100,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '0-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信終了
                'release_key' => 2024090100,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '0-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最古)
                'release_key' => 2024090101,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '100-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最古)
                'release_key' => 2024090101,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '100-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最新)
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'target_release_version_id' => '101-ios',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信中(最新)
                'release_key' => 2024090102,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'target_release_version_id' => '101-android',
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中
                'release_key' => 2024090103,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 0,
            ]);

        // Exercise
        $actuals = $this->mngAssetReleaseService->getMngAssetReleasesByApplyOrPending();

        // Verify
        $this->assertCount(6, $actuals);
        // 配信中(最古)
        $actualByIosAndApply1 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_IOS && $row->release_key === 2024090101);
        $this->assertEquals(2024090101, $actualByIosAndApply1->release_key);
        $this->assertTrue((bool) $actualByIosAndApply1->enabled);
        $this->assertEquals('100-ios', $actualByIosAndApply1->target_release_version_id);
        $actualByAndroidAndApply1 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_ANDROID && $row->release_key === 2024090101);
        $this->assertEquals(2024090101, $actualByAndroidAndApply1->release_key);
        $this->assertTrue((bool) $actualByAndroidAndApply1->enabled);
        $this->assertEquals('100-android', $actualByAndroidAndApply1->target_release_version_id);

        // 配信中(最新)
        $actualByIosAndApply2 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_IOS && $row->release_key === 2024090102);
        $this->assertEquals(2024090102, $actualByIosAndApply2->release_key);
        $this->assertTrue((bool) $actualByIosAndApply2->enabled);
        $this->assertEquals('101-ios', $actualByIosAndApply2->target_release_version_id);
        $actualByAndroidAndApply2 = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_ANDROID && $row->release_key === 2024090102);
        $this->assertEquals(2024090102, $actualByAndroidAndApply2->release_key);
        $this->assertTrue((bool) $actualByAndroidAndApply2->enabled);
        $this->assertEquals('101-android', $actualByAndroidAndApply2->target_release_version_id);

        // 配信準備中
        $actualByIosAndPending = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_IOS && $row->release_key === 2024090103);
        $this->assertEquals(2024090103, $actualByIosAndPending->release_key);
        $this->assertFalse((bool) $actualByIosAndPending->enabled);
        $this->assertNull($actualByIosAndPending->target_release_version_id);
        $actualByAndroidAndPending = $actuals->first(fn ($row) => $row->platform === PlatformConstant::PLATFORM_ANDROID && $row->release_key === 2024090103);
        $this->assertEquals(2024090103, $actualByAndroidAndPending->release_key);
        $this->assertFalse((bool) $actualByAndroidAndPending->enabled);
        $this->assertNull($actualByAndroidAndPending->target_release_version_id);
    }

    /**
     * @test
     */
    public function getLastImportAtMap_データ取得チェック(): void
    {
        // Setup
        $admAssetImportHistories = [
            [
                'id' => 'admAssetImportHistory1',
                'mng_asset_release_version_id' => 'mngAssetReleaseVersionId1',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090101',
                'created_at' => '2024-09-01 10:00:00',
                'updated_at' => '2024-09-01 10:00:00',
            ],
            [
                'id' => 'admAssetImportHistory2',
                'mng_asset_release_version_id' => 'mngAssetReleaseVersionId2',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090102',
                'created_at' => '2024-09-01 12:00:00',
                'updated_at' => '2024-09-01 12:00:00',
            ],
            [
                'id' => 'admAssetImportHistory3',
                'mng_asset_release_version_id' => 'mngAssetReleaseVersionId3',
                'import_adm_user_id' => 'admin_1',
                'import_source' => 'import_source_2024090201',
                'created_at' => '2024-09-01 12:00:01',
                'updated_at' => '2024-09-01 12:00:01',
            ],
        ];
        foreach ($admAssetImportHistories as $admAssetImportHistory) {
            // idを指定したもので登録したいのでinsertで生成
            AdmAssetImportHistory::query()
                ->insert($admAssetImportHistory);
        }

        // Exercise
        $actuals = $this->mngAssetReleaseService->getLastImportAtMap();

        // Verify
        $this->assertCount(3, $actuals);
        /** @var Illuminate\Support\Carbon $actual1 */
        $actual1 = $actuals['mngAssetReleaseVersionId1'];
        $this->assertEquals('2024-09-01 10:00:00', $actual1->format('Y-m-d H:i:s'));
        /** @var Illuminate\Support\Carbon $actual2 */
        $actual2 = $actuals['mngAssetReleaseVersionId2'];
        $this->assertEquals('2024-09-01 12:00:00', $actual2->format('Y-m-d H:i:s'));
        /** @var Illuminate\Support\Carbon $actual3 */
        $actual3 = $actuals['mngAssetReleaseVersionId3'];
        $this->assertEquals('2024-09-01 12:00:01', $actual3->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     */
    public function deleteAssetRelease_データ削除チェック(): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'asset_1',
                'target_release_version_id' => '101',
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                'id' => '101',
            ]);

        // Exercise
        $this->mngAssetReleaseService
            ->deleteAssetRelease($mngAssetRelease);

        // Verify
        $mngAssetReleases = MngAssetRelease::all();
        $this->assertCount(0, $mngAssetReleases);
        $mngAssetReleaseVersions = MngAssetReleaseVersion::all();
        $this->assertCount(0, $mngAssetReleaseVersions);
    }

    /**
     * @test
     */
    public function deleteAssetRelease_OprAssetReleaseのみ削除チェック(): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'asset_1',
            ]);

        // Exercise
        $this->mngAssetReleaseService
            ->deleteAssetRelease($mngAssetRelease);

        // Verify
        $mngAssetReleases = MngAssetRelease::all();
        $this->assertCount(0, $mngAssetReleases);
        $mngAssetReleaseVersions = MngAssetReleaseVersion::all();
        $this->assertCount(0, $mngAssetReleaseVersions);
    }

    /**
     * @test
     */
    public function releasedMngAssetReleasesById_更新チェック(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->create([
                // 現在配信中
                'release_key' => 2024080101,
                'target_release_version_id' => '101',
                'enabled' => 1,
            ]);
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                // 配信準備中(ios)
                'release_key' => 2024090101,
                'target_release_version_id' => '102-ios',
                'platform' => PlatformConstant::PLATFORM_IOS,
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中(android)
                'release_key' => 2024090101,
                'target_release_version_id' => '102-android',
                'platform' => PlatformConstant::PLATFORM_ANDROID,
            ]);
        MngAssetRelease::factory()
            ->create([
                // 配信準備中(リリース対象外)
                'release_key' => 2024100101,
            ]);

        // Exercise
        $this->mngAssetReleaseService
            ->releasedMngAssetReleasesById($mngAssetRelease->id);

        // Verify
        $actuals = MngAssetRelease::all();
        // enabledがtrueのままになっている
        $actual1 = $actuals->first(fn ($row) => $row->release_key === 2024080101);
        $this->assertTrue((bool) $actual1->enabled);
        // enabledがtrueになっている
        $actual2Ios = $actuals->first(fn ($row) => $row->release_key === 2024090101 && $row->platform === PlatformConstant::PLATFORM_IOS);
        $this->assertTrue((bool) $actual2Ios->enabled);
        // enabledがfalseのままになっている
        $actual2Android = $actuals->first(fn ($row) => $row->release_key === 2024090101 && $row->platform === PlatformConstant::PLATFORM_ANDROID);
        $this->assertFalse((bool) $actual2Android->enabled);
        $actual3 = $actuals->first(fn ($row) => $row->release_key === 2024100101);
        $this->assertFalse((bool) $actual3->enabled);
    }

    /**
     * @test
     */
    public function releasedMngAssetReleasesById_対象idが存在しない(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not found mng_asset_releases id:9999');

        // Exercise
        $this->mngAssetReleaseService
            ->releasedMngAssetReleasesById('9999');
    }

   /**
     * @test
     */
    public function getLatestReleaseKey_データ取得チェック(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        MngAssetRelease::factory()
            ->createMany([
                [
                    // 配信終了
                    'platform' => $platform,
                    'release_key' => 2024083101,
                    'enabled' => 1,
                    'target_release_version_id' => '99',
                ],
                [
                    // 配信中(最古)
                    'platform' => $platform,
                    'release_key' => 2024090101,
                    'enabled' => 1,
                    'target_release_version_id' => '100',
                ],
                [
                    // 配信中(最新)
                    'platform' => $platform,
                    'release_key' => 2024090102,
                    'enabled' => 1,
                    'target_release_version_id' => '101',
                ],
                [
                    // 配信準備中
                    'platform' => $platform,
                    'release_key' => 2024090103,
                    'enabled' => 0,
                    'target_release_version_id' => null,
                ],
            ]);

        // Exercise
        $actual = $this->mngAssetReleaseService->getLatestReleaseKey($platform);

        // Verify
        $this->assertEquals(2024090102, $actual);
    }

    /**
     * @test
     */
    public function getOldestApplyMngAssetReleaseKey_データ取得チェック(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        MngAssetRelease::factory()
            ->createMany([
                [
                    // 配信終了
                    'platform' => $platform,
                    'release_key' => 2024083101,
                    'enabled' => 1,
                    'target_release_version_id' => '99',
                ],
                [
                    // 配信中(最古)
                    'platform' => $platform,
                    'release_key' => 2024090101,
                    'enabled' => 1,
                    'target_release_version_id' => '100',
                ],
                [
                    // 配信中(最新)
                    'platform' => $platform,
                    'release_key' => 2024090102,
                    'enabled' => 1,
                    'target_release_version_id' => '101',
                ],
                [
                    // 配信準備中
                    'platform' => $platform,
                    'release_key' => 2024090103,
                    'enabled' => 0,
                    'target_release_version_id' => null,
                ],
            ]);

        // Exercise
        $actual = $this->mngAssetReleaseService->getOldestApplyMngAssetReleaseKey($platform);

        // Verify
        $this->assertEquals(2024090101, $actual);
    }

    /**
     * @test
     */
    public function getOldestApplyMngAssetReleaseKey_データ取得チェック_配信中データなし(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        MngAssetRelease::factory()
            ->createMany([
                [
                    // 配信準備中
                    'platform' => $platform,
                    'release_key' => 202409010,
                    'enabled' => 0,
                    'target_release_version_id' => 100,
                ],
            ]);

        // Exercise
        $actual = $this->mngAssetReleaseService->getOldestApplyMngAssetReleaseKey($platform);

        // Verify
        $this->assertEquals(0, $actual);
    }

    /**
     * @test
     */
    public function getEffectiveAssetReleaseList_データ取得チェック(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $latestReleaseKey = 2024090102;

        $mngAssetReleases = [
            [
                // 配信終了
                'platform' => $platform,
                'release_key' => 2024083101,
                'enabled' => 1,
                'target_release_version_id' => '1',
            ],
            [
                // 配信中(最古)
                'platform' => $platform,
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中(最新)
                'platform' => $platform,
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'platform' => $platform,
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        MngAssetRelease::factory()
            ->createMany($mngAssetReleases);

        // Exercise
        $actuals = $this->mngAssetReleaseService->getEffectiveAssetReleaseList($platform, $latestReleaseKey);

        // Verify
        $this->assertCount(3, $actuals);
        $actualArray = $actuals->toArray();
        // 「配信中」「準備中」のレコードのみが取得できる
        $this->assertEquals(2024090103, $actualArray[0]['release_key']);
        $this->assertEquals(2024090102, $actualArray[1]['release_key']);
        $this->assertEquals(2024090101, $actualArray[2]['release_key']);
    }

    /**
     * @test
     */
    public function getMngAssetReleaseByReleaseKey_データ取得チェック_latestReleaseKeyと指定releaseKeyが同じ(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $latestReleaseKey = 2024090102;
        $targetReleaseKey = 2024090102;

        $mngAssetReleases = [
            [
                // 配信終了
                'platform' => $platform,
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中
                'platform' => $platform,
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'platform' => $platform,
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
            [
                // 配信準備中
                'platform' => $platform,
                'release_key' => 2024090104,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        foreach ($mngAssetReleases as $data) {
            MngAssetRelease::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->getMngAssetReleaseByReleaseKey($platform, $targetReleaseKey, $latestReleaseKey);

        // Verify
        $this->assertNotNull($actual);
        $actualArray = $actual->toArray();
        // 指定release_key情報が取得できていること
        $this->assertEquals($targetReleaseKey, $actualArray['release_key']);
        $this->assertEquals(101, $actualArray['target_release_version_id']);
    }

    /**
     * @test
     */
    public function getMngAssetReleaseByReleaseKey_データ取得チェック_latestReleaseKeyと指定releaseKeyが異なる(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $latestReleaseKey = 2024090102;
        $targetReleaseKey = 2024090103;

        $mngAssetReleases = [
            [
                // 配信終了
                'platform' => $platform,
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中
                'platform' => $platform,
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'platform' => $platform,
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
            [
                // 配信準備中
                'platform' => $platform,
                'release_key' => 2024090104,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        foreach ($mngAssetReleases as $data) {
            MngAssetRelease::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->getMngAssetReleaseByReleaseKey($platform, $targetReleaseKey, $latestReleaseKey);

        // Verify
        $this->assertNotNull($actual);
        $actualArray = $actual->toArray();
        // 指定release_key情報が取得できていること
        $this->assertEquals($targetReleaseKey, $actualArray['release_key']);
        $this->assertNull($actualArray['target_release_version_id']);
    }

    /**
     * @test
     */
    public function getMngAssetReleaseByReleaseKey_データ取得チェック_配信中準備中以外のステータスのreleaseKeyを指定(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $latestReleaseKey = 2024090102;
        $targetReleaseKey = 2024083101;

        $mngAssetReleases = [
            [
                // 配信終了
                'platform' => $platform,
                'release_key' => 2024083101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中(最古)
                'platform' => $platform,
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中(最新)
                'platform' => $platform,
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'platform' => $platform,
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
            [
                // 配信準備中
                'platform' => $platform,
                'release_key' => 2024090104,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        MngAssetRelease::factory()
            ->createMany($mngAssetReleases);

        // Exercise
        $actual = $this->mngAssetReleaseService->getMngAssetReleaseByReleaseKey($platform, $targetReleaseKey, $latestReleaseKey);

        // Verify
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function getAssetReleaseVersionById_データ取得チェック_idがnull(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $id = null;

        $mngAssetReleaseVersions = [
            [
                'id' => 1,
                'release_key' => 2024112701,
                'platform' => $platform,
                'catalog_hash' => "test_hash1",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => 2,
                'release_key' => 2024112601,
                'platform' => $platform,
                'catalog_hash' => "test_hash2",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => 3,
                'release_key' => 2024112801,
                'platform' => $platform,
                'catalog_hash' => "test_hash3",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
        ];
        foreach ($mngAssetReleaseVersions as $data) {
            MngAssetReleaseVersion::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->getAssetReleaseVersionById($id);

        // Verify
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function getAssetReleaseVersionById_データ取得チェック_idあり_データあり(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $id = '2';

        $mngAssetReleaseVersions = [
            [
                'id' => 1,
                'release_key' => 2024112701,
                'platform' => $platform,
                'catalog_hash' => "test_hash1",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => 2,
                'release_key' => 2024112601,
                'platform' => $platform,
                'catalog_hash' => "test_hash2",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => 3,
                'release_key' => 2024112801,
                'platform' => $platform,
                'catalog_hash' => "test_hash3",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
        ];
        foreach ($mngAssetReleaseVersions as $data) {
            MngAssetReleaseVersion::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->getAssetReleaseVersionById($id);

        // Verify
        $this->assertNotNull($actual);
        $this->assertEquals(2024112601, $actual['release_key']);
        $this->assertEquals("test_hash2", $actual['catalog_hash']);
    }

    /**
     * @test
     */
    public function getAssetReleaseVersionById_データ取得チェック_idあり_データなし(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $id = '4';

        $mngAssetReleaseVersions = [
            [
                'id' => 1,
                'release_key' => 2024112701,
                'platform' => $platform,
                'catalog_hash' => "test_hash1",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => 2,
                'release_key' => 2024112601,
                'platform' => $platform,
                'catalog_hash' => "test_hash2",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => 3,
                'release_key' => 2024112801,
                'platform' => $platform,
                'catalog_hash' => "test_hash3",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
        ];
        foreach ($mngAssetReleaseVersions as $data) {
            MngAssetReleaseVersion::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->getAssetReleaseVersionById($id);

        // Verify
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function createAssetFileDirectoryPathAndGetAssetInfo_データ取得と作成したパスが正しいかチェック(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $releaseKey = 2024120601;
        $environment = 'testing';

        $mngAssetReleases = [
            [
                'release_key' => 2024120601,
                'target_release_version_id' => '101-ios',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'description' => "test1",
            ],
            [
                'release_key' => 2024120601,
                'target_release_version_id' => '101-android',
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'description' => "test2",
            ]
        ];
        foreach ($mngAssetReleases as $data) {
            MngAssetRelease::query()
                ->create($data);
        }

        $mngAssetReleaseVersions = [
            [
                'id' => "101-ios",
                'release_key' => 2024120601,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => "test_hash1",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => '101-android',
                'release_key' => 2024120601,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => "test_hash2",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
        ];
        foreach ($mngAssetReleaseVersions as $data) {
            MngAssetReleaseVersion::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->createAssetFileDirectoryPathAndGetAssetInfo($environment, $platform, $releaseKey);

        // Verify
        $this->assertNotEmpty($actual);
        $assetInfoActual = $actual['asset_info'];
        $pathActual = $actual['path'];
        $expectedAssetInfo = [
            'platform' => $platform,
            'release_key' => $releaseKey,
            'status' => ReleaseStatus::RELEASE_STATUS_APPLYING->value,
            'git_revision' => "test",
            'catalog_hash' => "test_hash1",
            'description' => "test1",
        ];
        $this->assertEquals($expectedAssetInfo, $assetInfoActual);
        $expectedPath = '/assetbundles/ios/test_hash1';
        $this->assertEquals($expectedPath, $pathActual);
    }

    /**
     * @test
     */
    public function createAssetFileDirectoryPathAndGetAssetInfo_自環境のreleaseKeyにversion情報がない時配信中の最新リリースキーの情報を取得できているか確認(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $releaseKey = 2024121701;
        $environment = 'testing';

        $mngAssetReleases = [
            [
                'release_key' => 2024121701,
                'target_release_version_id' => NULL,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
                'description' => "test1",
            ],
            [
                'release_key' => 2024120601,
                'target_release_version_id' => '101-ios',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
                'description' => "test2",
            ],
            [
                'release_key' => 2024120601,
                'target_release_version_id' => '101-android',
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
                'description' => "test3",
            ]
        ];
        foreach ($mngAssetReleases as $data) {
            MngAssetRelease::query()
                ->create($data);
        }

        $mngAssetReleaseVersions = [
            [
                'id' => "101-ios",
                'release_key' => 2024120601,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => "test_hash1",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => '101-android',
                'release_key' => 2024120601,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => "test_hash2",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
        ];
        foreach ($mngAssetReleaseVersions as $data) {
            MngAssetReleaseVersion::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->createAssetFileDirectoryPathAndGetAssetInfo($environment, $platform, $releaseKey);

        // Verify
        $this->assertNotEmpty($actual);
        $assetInfoActual = $actual['asset_info'];
        $pathActual = $actual['path'];
        // 配信中のversionデータが返ってくること
        $expectedAssetInfo = [
            'platform' => $platform,
            'release_key' => 2024120601,
            'status' => ReleaseStatus::RELEASE_STATUS_APPLYING->value,
            'git_revision' => "test",
            'catalog_hash' => "test_hash1",
            'description' => "test2",
        ];
        $this->assertEquals($expectedAssetInfo, $assetInfoActual);
        $expectedPath = '/assetbundles/ios/test_hash1';
        $this->assertEquals($expectedPath, $pathActual);
    }

    /**
     * @test
     */
    public function createAssetFileDirectoryPathAndGetAssetInfo_自環境で選択したreleaseKeyのversion情報がない且つ配信中もない時(): void
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $releaseKey = 2024121701;
        $environment = 'testing';

        $mngAssetReleases = [
            [
                'release_key' => 2024121701,
                'target_release_version_id' => NULL,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 0,
                'description' => "test1",
            ]
        ];
        foreach ($mngAssetReleases as $data) {
            MngAssetRelease::query()
                ->create($data);
        }

        // Exercise
        $actual = $this->mngAssetReleaseService->createAssetFileDirectoryPathAndGetAssetInfo($environment, $platform, $releaseKey);

        // Verify
        $this->assertNotEmpty($actual);
        $assetInfoActual = $actual['asset_info'];
        $pathActual = $actual['path'];

        // 返ってくるアセット情報
        $expectedAssetInfo = [
            'platform' => $platform,
            'release_key' => 2024121701,
            'status' => ReleaseStatus::RELEASE_STATUS_PENDING->value,
            'git_revision' => '',
            'catalog_hash' => '',
            'description' => "test1",
        ];
        $this->assertEquals($expectedAssetInfo, $assetInfoActual);
        $this->assertEquals('', $pathActual);
    }


    /**
     * @test
     */
    public function insertReleaseVersionAndUpdateTargetId_更新と登録ができること()
    {
        // Setup
        $platform = PlatformConstant::PLATFORM_IOS;
        $releaseKey = 2024120601;

        $mngAssetReleases = [
            [
                'release_key' => 2024120601,
                'target_release_version_id' => '101-ios',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'enabled' => 1,
            ],
            [
                'release_key' => 2024120601,
                'target_release_version_id' => '101-android',
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'enabled' => 1,
            ]
        ];
        foreach ($mngAssetReleases as $data) {
            MngAssetRelease::query()
                ->create($data);
        }

        $mngAssetReleaseVersions = [
            [
                'id' => "101-ios",
                'release_key' => 2024120601,
                'platform' => PlatformConstant::PLATFORM_IOS,
                'catalog_hash' => "test_hash1",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
            [
                'id' => '101-android',
                'release_key' => 2024120601,
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'catalog_hash' => "test_hash2",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
        ];
        foreach ($mngAssetReleaseVersions as $data) {
            MngAssetReleaseVersion::query()
                ->create($data);
        }

        $fromMngAssetReleaseVersion = [
            'id' => 'test',
            'release_key' => 2024120601,
            'platform' => PlatformConstant::PLATFORM_IOS,
            'catalog_hash' => "test_hash3",
            'git_revision' => "test",
            'git_branch' => "test",
            'build_client_version' => "test",
            'asset_total_byte_size' => 100,
            'catalog_byte_size' => 100,
            'catalog_file_name' => "test",
            'catalog_hash_file_name' => "test",
        ];

        // Exercise
        $this->mngAssetReleaseService->insertReleaseVersionAndUpdateTargetId(
            $releaseKey,
            $platform,
            collect($fromMngAssetReleaseVersion)
        );

        // Verify
        // mng_asset_releasesのtarget_release_version_idが更新されていること
        $actual = MngAssetRelease::query()->where('platform', PlatformConstant::PLATFORM_IOS)->get()->first()->toArray();
        $actualTargetId = $actual['target_release_version_id'];
        $this->assertNotEquals('101-ios', $actualTargetId);
        // mng_asset_release_versionsデータがinsertされていること
        $actual = MngAssetReleaseVersion::query()->where('id', $actualTargetId)->get()->first();
        $this->assertNotNull($actual);
        $actualData = $actual->toArray();
        $this->assertEquals($fromMngAssetReleaseVersion['release_key'], $actualData['release_key']);
        $this->assertEquals($fromMngAssetReleaseVersion['platform'], $actualData['platform']);
        $this->assertEquals($fromMngAssetReleaseVersion['catalog_hash'], $actualData['catalog_hash']);
        $this->assertEquals($fromMngAssetReleaseVersion['git_revision'], $actualData['git_revision']);
        $this->assertEquals($fromMngAssetReleaseVersion['git_branch'], $actualData['git_branch']);
        $this->assertEquals($fromMngAssetReleaseVersion['build_client_version'], $actualData['build_client_version']);
        $this->assertEquals($fromMngAssetReleaseVersion['asset_total_byte_size'], $actualData['asset_total_byte_size']);
        $this->assertEquals($fromMngAssetReleaseVersion['catalog_byte_size'], $actualData['catalog_byte_size']);
        $this->assertEquals($fromMngAssetReleaseVersion['catalog_file_name'], $actualData['catalog_file_name']);
        $this->assertEquals($fromMngAssetReleaseVersion['catalog_hash_file_name'], $actualData['catalog_hash_file_name']);
    }
}
