<?php

namespace MasterAssetReleaseAdmin\Unit\Entities;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Tests\TestCase;

class MngMasterReleaseKeyEntityTest extends TestCase
{
    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function getReleaseKeys_取得チェック(): void
    {
        // Setup
        $releaseKeyByStatusApply = 2024090102;
        $mngMasterReleases = [
            [
                // 配信終了
                'id' => '1',
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中
                'id' => 2,
                'release_key' => $releaseKeyByStatusApply,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'id' => 3,
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => null,
            ],
        ];
        $mngMasterReleasesByApplyOrPending = [];
        foreach ($mngMasterReleases as $data) {
            $mngMasterRelease = MngMasterRelease::factory()
                ->create($data);
            if (in_array($data['id'], [2,3], true)) {
                // 配信中/配信準備中のデータを取得
                $mngMasterReleasesByApplyOrPending[] = $mngMasterRelease;
            }
        }

        // Exercise
        $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity($releaseKeyByStatusApply, collect($mngMasterReleasesByApplyOrPending));
        $actuals = $mngMasterReleaseKeyEntity->getReleaseKeys();

        // Verify
        // 配信中と配信準備中の2件が取得できているか
        $this->assertCount(2, $actuals);
        foreach ($actuals as $releaseKey) {
            if (!in_array($releaseKey, [2024090102, 2024090103], true)) {
                // 配信中/配信準備中のリリースキー以外が取得されていたらエラー
                $this->fail();
            }
        }
    }

    /**
     * @test
     */
    public function getMasterDbNameParameter_配信中の情報から取得(): void
    {
        // Setup
        $releaseKeyByStatusApply = 2024090102;
        $mngMasterReleases = [
            [
                // 配信終了
                'id' => 1,
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '100',
            ],
            [
                // 配信中
                'id' => 2,
                'release_key' => $releaseKeyByStatusApply,
                'enabled' => 1,
                'target_release_version_id' => '101',
            ],
            [
                // 配信準備中
                'id' => 3,
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '102',
            ],
        ];
        $mngMasterReleasesByApplyOrPending = [];
        foreach ($mngMasterReleases as $data) {
            $mngMasterRelease = MngMasterRelease::factory()
                ->create($data);
            if (in_array($data['id'], [2,3], true)) {
                // 配信中/配信準備中のデータを取得
                $mngMasterReleasesByApplyOrPending[] = $mngMasterRelease;
            }
        }
        $mngMasterReleaseVersions = [
            [
                'id' => '100',
                'release_key' => 2024090101,
                'server_db_hash' => 'revision1',
            ],
            [
                'id' => '101',
                'release_key' => $releaseKeyByStatusApply,
                'server_db_hash' => 'revision2',
            ],
            [
                'id' => '102',
                'release_key' => 2024090103,
                'server_db_hash' => 'revision3',
            ],
        ];
        // 配信終了&配信中データを作成
        foreach ($mngMasterReleaseVersions as $data) {
            MngMasterReleaseVersion::factory()
                ->create($data);
        }

        // Exercise
        $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity($releaseKeyByStatusApply, collect($mngMasterReleasesByApplyOrPending));
        $actuals = $mngMasterReleaseKeyEntity->getMasterDbNameParameter();

        // Verify
        // 配信中のデータから取得できているか
        $this->assertEquals([
            'releaseKey' => '2024090102',
            'serverDbHashMap' => [
                '2024090102' => 'revision2',
            ],
        ], $actuals);
    }

    /**
     * @test
     */
    public function getMasterDbNameParameter_配信準備中の情報から取得(): void
    {
        // Setup
        $mngMasterReleases = [
            [
                // 配信準備中1
                'release_key' => 2024090101,
                'enabled' => 0,
                'target_release_version_id' => '100',
            ],
            [
                // 配信準備中2
                'release_key' => 2024090102,
                'enabled' => 0,
                'target_release_version_id' => '101',
            ],
        ];
        $mngMasterReleasesByApplyOrPending = [];
        foreach ($mngMasterReleases as $data) {
            $mngMasterRelease = MngMasterRelease::factory()
                ->create($data);
            $mngMasterReleasesByApplyOrPending[] = $mngMasterRelease;
        }
        $mngMasterReleaseVersions = [
            [
                'id' => '100',
                'release_key' => 2024090101,
                'server_db_hash' => 'revision1',
            ],
            [
                'id' => '101',
                'release_key' => 2024090102,
                'server_db_hash' => 'revision2',
            ],
        ];
        // 配信準備中データを作成
        foreach ($mngMasterReleaseVersions as $data) {
            MngMasterReleaseVersion::factory()
                ->create($data);
        }

        // Exercise
        $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity(0, collect($mngMasterReleasesByApplyOrPending));
        $actuals = $mngMasterReleaseKeyEntity->getMasterDbNameParameter();

        // Verify
        // 配信準備中2のリリースキーを取得できているか
        $this->assertEquals([
            'releaseKey' => '2024090102',
            'serverDbHashMap' => [
                '2024090102' => 'revision2',
            ],
        ], $actuals);
    }

    /**
     * @test
     */
    public function getMasterDbNameParameter_serverDbHashがnullの場合の取得(): void
    {
        // Setup
        // 配信準備中(リリースキーを追加したところまで)
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'release_key' => 2024090101,
                'enabled' => 0,
                'target_release_version_id' => '100',
            ]);

        // Exercise
        $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity(0, collect([$mngMasterRelease]));
        $actuals = $mngMasterReleaseKeyEntity->getMasterDbNameParameter();

        // Verify
        // 配信準備中+空文字でリリースキーを取得できているか
        $this->assertEquals([
            'releaseKey' => '2024090101',
            'serverDbHashMap' => [
                '2024090101' => '',
            ],
        ], $actuals);
    }
}
