<?php

namespace Filament\Resources\MngMasterReleaseImportResource\Pages;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource\Pages\DiffFromEnvironment;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Tests\TestCase;

class DiffFromEnvironmentTest extends TestCase
{
    use ReflectionTrait;
    
    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function setReleaseDiffData_表示データ生成チェック(): void
    {
        $diffFromEnvironment = new DiffFromEnvironment();
        $apply = MngMasterRelease::factory()
            ->create([
                // 配信中
                'id' => 'master-1',
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '101',
                'description' => '2024年9月配信版'
            ]);
        $applyVersions = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090101,
            ]);
        $pending = MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-2',
                'release_key' => 2024090102,
                'enabled' => 0,
                'target_release_version_id' => '102',
                'description' => '2024年9月修正版'
            ]);
        $pendingVersions = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090102,
            ]);
        $pendingNoVersions = MngMasterRelease::factory()
            ->create([
                // 配信準備中(リリースデータなし)
                'id' => 'master-3',
                'release_key' => 2024090103,
                'enabled' => 0,
            ]);

        $diffFromEnvironment->applyMngMasterReleases = collect([$apply]);
        $diffFromEnvironment->mngMasterReleasesByApplyOrPending = collect([$pending, $apply, $pendingNoVersions]);
        $diffFromEnvironment->fromEnvironmentMasterReleaseList = collect([
            [
                'release_key' => 2024090101,
                'mng_master_release_versions' => [
                    'data_hash' => 'env_data_hash_2024090101',
                    'git_revision' => 'env_git_revision_2024090101'
                ]
            ],
            [
                'release_key' => 2024090102,
                'mng_master_release_versions' => [
                    'data_hash' => 'env_data_hash_2024090102',
                    'git_revision' => 'env_git_revision_2024090102'
                ]
            ],
        ]);

        // Exercise
        $this->callMethod($diffFromEnvironment, 'setReleaseDiffData');
        $actual = $diffFromEnvironment->releaseDiffData;

        // Verify
        $actualApply = collect($actual)->first(fn ($row) => $row['releaseKey'] === $apply->release_key);
        $this->assertEquals([
            'releaseKey' => $apply->release_key,
            'status' => '配信中',
            'statusColor' => 'color: deeppink',
            'oldDataHash' => $applyVersions->data_hash,
            'newDataHash' => 'env_data_hash_2024090101',
            'description' => $apply->description,
            'oldGitRevision' =>  $applyVersions->git_revision,
            'newGitRevision' => 'env_git_revision_2024090101',
        ], $actualApply);
        $actualPending = collect($actual)->first(fn ($row) => $row['releaseKey'] === $pending->release_key);
        $this->assertEquals([
            'releaseKey' => $pending->release_key,
            'status' => '配信準備中',
            'statusColor' => 'color: darkolivegreen',
            'oldDataHash' => $pendingVersions->data_hash,
            'newDataHash' => 'env_data_hash_2024090102',
            'description' => $pending->description,
            'oldGitRevision' => $pendingVersions->git_revision,
            'newGitRevision' => 'env_git_revision_2024090102',
        ], $actualPending);
        $actualPendingNoVersions = collect($actual)->first(fn ($row) => $row['releaseKey'] === $pendingNoVersions->release_key);
        $this->assertEquals([
            'releaseKey' => $pendingNoVersions->release_key,
            'status' => '配信準備中',
            'statusColor' => 'color: darkolivegreen',
            'oldDataHash' => '(設定なし)',
            'newDataHash' => '(変更なし)',
            'description' => $pendingNoVersions->description,
            'oldGitRevision' => '(設定なし)',
            'newGitRevision' => '(変更なし)',
        ], $actualPendingNoVersions);
    }

    /**
     * @test
     */
    public function getGitRevisionFromSelfEnv_配信中リリースキーからの取得チェック(): void
    {
        // Setup
        // 自環境のリリースキー設定
        $mngMasterRelease1 = MngMasterRelease::factory()->create([
            // 配信中
            'release_key' => 202411010,
            'enabled' => 1,
            'description' => '2024年11月配信テスト',
            'target_release_version_id' => '100',
        ]);
        $version = MngMasterReleaseVersion::factory()->create([
            'id' => '100',
            'release_key' => 202411010,
        ]);
        $mngMasterRelease2 = MngMasterRelease::factory()->create([
            // 配信準備中(インポートデータあり)
            'release_key' => 202412010,
            'enabled' => 0,
            'description' => '2024年12月配信テスト',
            'target_release_version_id' => '101',
        ]);
        MngMasterReleaseVersion::factory()->create([
            'id' => '101',
            'release_key' => 202412010,
        ]);
        $mngMasterRelease3 = MngMasterRelease::factory()->create([
            // 配信準備中(インポートデータなし)
            'release_key' => 202412020,
            'enabled' => 0,
            'description' => '2024年12月配信テスト2回目',
        ]);

        // Exercise
        $diffFromEnvironment = new DiffFromEnvironment();
        $diffFromEnvironment->mngMasterReleasesByApplyOrPending = collect([$mngMasterRelease1, $mngMasterRelease2, $mngMasterRelease3]);
        $actual = $this->callMethod(
            $diffFromEnvironment,
            'getGitRevisionFromSelfEnv',
        );

        // Verify
        // 配信中のリリースキーのgit_revisionと一致するか
        $this->assertEquals($version->git_revision, $actual);
    }

    /**
     * @test
     */
    public function getGitRevisionFromSelfEnv_配信準備中リリースキーからの取得チェック(): void
    {
        // Setup
        // 自環境のリリースキー設定
        $mngMasterRelease1 = MngMasterRelease::factory()->create([
            // 配信準備中(インポートデータあり)
            'release_key' => 202411010,
            'enabled' => 0,
            'description' => '2024年11月配信テスト',
            'target_release_version_id' => '100',
        ]);
        $version = MngMasterReleaseVersion::factory()->create([
            'id' => '100',
            'release_key' => 202411010,
        ]);
        $mngMasterRelease2 = MngMasterRelease::factory()->create([
            // 配信準備中(インポートデータなし)
            'release_key' => 202412020,
            'enabled' => 0,
            'description' => '2024年12月配信テスト2回目',
        ]);

        // Exercise
        $diffFromEnvironment = new DiffFromEnvironment();
        $diffFromEnvironment->mngMasterReleasesByApplyOrPending = collect([$mngMasterRelease1, $mngMasterRelease2]);
        $actual = $this->callMethod(
            $diffFromEnvironment,
            'getGitRevisionFromSelfEnv',
        );

        // Verify
        // 配信準備中のリリースキーのgit_revisionと一致するか
        $this->assertEquals($version->git_revision, $actual);
    }

    /**
     * @test
     */
    public function getGitRevisionFromSelfEnv_未インポートでは空文字となりフラグが更新されるかチェック(): void
    {
        // Exercise
        $diffFromEnvironment = new DiffFromEnvironment();
        $diffFromEnvironment->mngMasterReleasesByApplyOrPending = collect();
        $actual = $this->callMethod(
            $diffFromEnvironment,
            'getGitRevisionFromSelfEnv',
        );

        // Verify
        // 空文字になっているか
        $this->assertEquals('', $actual);
        $this->assertTrue($diffFromEnvironment->isFirstImport);
    }

    /**
     * @test
     */
    public function setFromEnvironmentData_対象環境のリリース情報から情報を生成できるかチェック(): void
    {
        // Setup
        $fromEnvironment = 'test';
        // 自環境のリリース情報
        $mngMasterRelease = MngMasterRelease::factory()->create([
            'release_key' => 202411010,
            'enabled' => 0,
            'target_release_version_id' => '101',
        ]);
        // インポート元環境のマスターデータリリース情報
        $effectiveMasterReleaseList = collect([
            [
                'release_key' => 202411010,
                'description' => '2024年11月配信テスト',
                'enabled' => 1,
                'is_latest_version' => true,
                'mng_master_release_versions' => [
                    'release_key' => 202411010,
                    'git_revision' => 'git_revision_202411010',
                    'master_schema_version' => 'master_schema_version_202411010',
                    'data_hash' => 'data_hash_202411010',
                    'server_db_hash' => 'server_db_hash_202411010',
                    'client_mst_data_hash' => 'client_mst_data_hash_202411010',
                    'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202411010',
                    'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202411010',
                    'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202411010',
                    'client_opr_data_hash' => 'client_opr_data_hash_202411010',
                    'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202411010',
                    'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202411010',
                    'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202411010',
                ],
            ],
        ]);
        $mngMasterReleaseServiceMock = $this->createMock(MngMasterReleaseService::class);
        $mngMasterReleaseServiceMock->method('getEffectiveMasterReleaseListFromEnvironment')
            ->with($fromEnvironment, [202411010])
            ->willReturn($effectiveMasterReleaseList);

        app()->instance(MngMasterReleaseService::class, $mngMasterReleaseServiceMock);
        $diffFromEnvironment = new DiffFromEnvironment();
        $diffFromEnvironment->fromEnvironment = $fromEnvironment;
        $diffFromEnvironment->mngMasterReleasesByApplyOrPending = collect([$mngMasterRelease]);

        // Exercise
        $this->callMethod(
            $diffFromEnvironment,
            'setFromEnvironmentData',
        );

        // Verify
        $this->assertEquals(
            [
                'release_key' => 202411010,
                'git_revision' => 'git_revision_202411010',
                'master_schema_version' => 'master_schema_version_202411010',
                'data_hash' => 'data_hash_202411010',
                'server_db_hash' => 'server_db_hash_202411010',
                'client_mst_data_hash' => 'client_mst_data_hash_202411010',
                'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202411010',
                'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202411010',
                'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202411010',
                'client_opr_data_hash' => 'client_opr_data_hash_202411010',
                'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202411010',
                'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202411010',
                'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202411010',
            ],
            $diffFromEnvironment->fromEnvironmentMngMasterReleaseVersion
        );
    }

    /**
     * @test
     */
    public function setFromEnvironmentData_対象環境のリリース情報が空だった場合のチェック(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('error: fromEnvironmentGitRevision is null fromEnvironment:test');
        // Setup
        $fromEnvironment = 'test';
        // 自環境のリリース情報
        $mngMasterRelease = MngMasterRelease::factory()->create([
            'release_key' => 202411010,
            'enabled' => 0,
            'target_release_version_id' => '101',
        ]);
        // インポート元環境のマスターデータリリース情報
        $effectiveMasterReleaseList = collect([]);
        $mngMasterReleaseServiceMock = $this->createMock(MngMasterReleaseService::class);
        $mngMasterReleaseServiceMock->method('getEffectiveMasterReleaseListFromEnvironment')
            ->with($fromEnvironment, [202411010])
            ->willReturn($effectiveMasterReleaseList);

        app()->instance(MngMasterReleaseService::class, $mngMasterReleaseServiceMock);
        $diffFromEnvironment = new DiffFromEnvironment();
        $diffFromEnvironment->fromEnvironment = $fromEnvironment;
        $diffFromEnvironment->mngMasterReleasesByApplyOrPending = collect([$mngMasterRelease]);

        // Exercise
        $this->callMethod(
            $diffFromEnvironment,
            'setFromEnvironmentData',
        );
    }

    /**
     * @test
     * @dataProvider getFromEnvironmentMngMasterReleaseVersionData
     */
    public function getFromEnvironmentMngMasterReleaseVersion_取得データチェック(int $releaseKey, array $expected): void
    {
        // Setup
        $diffFromEnvironment = new DiffFromEnvironment();
        $diffFromEnvironment->fromEnvironmentMasterReleaseList = collect([
            [
                'release_key' => 2024090101,
                'mng_master_release_versions' => [
                    'data_hash' => 'env_data_hash_2024090101',
                    'git_revision' => 'env_git_revision_2024090101'
                ]
            ],
            [
                'release_key' => 2024090102,
                'mng_master_release_versions' => [
                    'data_hash' => 'env_data_hash_2024090102',
                    'git_revision' => 'env_git_revision_2024090102'
                ]
            ],
        ]);

        // Exercise
        $actual = $this->callMethod(
            $diffFromEnvironment,
            'getFromEnvironmentMngMasterReleaseVersion',
            $releaseKey
        );

        // Verify
        // 想定したリリース情報を取得できているか
        // 一致するリリースキーがなかった場合、空配列になっているか
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getFromEnvironmentMngMasterReleaseVersionData(): array
    {
        return [
            'リリースキーが2024090101' => [
                2024090101,
                [
                    'data_hash' => 'env_data_hash_2024090101',
                    'git_revision' => 'env_git_revision_2024090101',
                ],
            ],
            'リリースキーが2024090102' => [
                2024090102,
                [
                    'data_hash' => 'env_data_hash_2024090102',
                    'git_revision' => 'env_git_revision_2024090102',
                ],
            ],
            'リリースキーが2024090103' => [
                2024090103,
                [],
            ],
        ];
    }
}
