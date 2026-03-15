<?php

declare(strict_types=1);

namespace Filament\Resources\MngMasterReleaseImportResource\Pages;

use Livewire\Livewire;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource\Pages\ImportMngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Tests\TestCase;

class ImportMngMasterReleaseTest extends TestCase
{
    use ReflectionTrait;
    
    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        Livewire::test(ImportMngMasterRelease::class)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function setTargetMasterReleases_インポート内容表示データ生成チェック(): void
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
        MngMasterReleaseVersion::factory()->create([
            'id' => '100',
            'release_key' => 202411010,
        ]);
        $mngMasterRelease2 = MngMasterRelease::factory()->create([
            // 配信準備中(インポートデータなし)
            'release_key' => 202412010,
            'enabled' => 0,
            'description' => '2024年12月配信テスト',
        ]);
        $fromEnvironment = 'test';
        // インポート元環境のマスターデータリリース情報
        $effectiveMasterReleaseList = collect([
            [
                'release_key' => 202412011,
                'description' => '2024年12月配信テスト修正版',
                'enabled' => 0,
                'is_latest_version' => false,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 202412011,
                    'git_revision' => 'git_revision_202412011',
                    'master_schema_version' => 'master_schema_version_202412011',
                    'data_hash' => 'data_hash_202412011',
                    'server_db_hash' => 'server_db_hash_202412011',
                    'client_mst_data_hash' => 'client_mst_data_hash_202412011',
                    'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202412011',
                    'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202412011',
                    'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202412011',
                    'client_opr_data_hash' => 'client_opr_data_hash_202412011',
                    'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202412011',
                    'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202412011',
                    'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202412011',
                ],
            ],
            [
                'release_key' => 202412010,
                'description' => '2024年12月配信テスト',
                'enabled' => 0,
                'is_latest_version' => true,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 202412010,
                    'git_revision' => 'git_revision_202412010',
                    'master_schema_version' => 'master_schema_version_202412010',
                    'data_hash' => 'data_hash_202412010',
                    'server_db_hash' => 'server_db_hash_202412010',
                    'client_mst_data_hash' => 'client_mst_data_hash_202412010',
                    'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202412010',
                    'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202412010',
                    'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202412010',
                    'client_opr_data_hash' => 'client_opr_data_hash_202412010',
                    'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202412010',
                    'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202412010',
                    'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202412010',
                ],
            ],
            [
                'release_key' => 202411010,
                'description' => '2024年11月配信テスト',
                'enabled' => 1,
                'is_latest_version' => true,
                'is_end_release' => true,
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
            ->with($fromEnvironment)
            ->willReturn($effectiveMasterReleaseList);

        // Exercise
        app()->instance(MngMasterReleaseService::class, $mngMasterReleaseServiceMock);
        $importMngMasterRelease = new ImportMngMasterRelease();
        $importMngMasterRelease->mngMasterReleaseCollection = collect([
            $mngMasterRelease2,
            $mngMasterRelease1,
        ]);
        $this->callMethod(
            $importMngMasterRelease,
            'setTargetMasterReleases',
            $fromEnvironment,
        );

        // Verify
        //  自環境に配信中のリリースキー以上のリリース情報でフィルタリングして取得できているか
        $actual = $importMngMasterRelease->masterReleaseArrayFromEnvironment;
        $this->assertEquals([
            [
                'release_key' => 202412011,
                'description' => '2024年12月配信テスト修正版',
                'enabled' => 0,
                'is_latest_version' => false,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 202412011,
                    'git_revision' => 'git_revision_202412011',
                    'master_schema_version' => 'master_schema_version_202412011',
                    'data_hash' => 'data_hash_202412011',
                    'server_db_hash' => 'server_db_hash_202412011',
                    'client_mst_data_hash' => 'client_mst_data_hash_202412011',
                    'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202412011',
                    'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202412011',
                    'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202412011',
                    'client_opr_data_hash' => 'client_opr_data_hash_202412011',
                    'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202412011',
                    'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202412011',
                    'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202412011',
                ],
            ],
            [
                'release_key' => 202412010,
                'description' => '2024年12月配信テスト',
                'enabled' => 0,
                'is_latest_version' => true,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 202412010,
                    'git_revision' => 'git_revision_202412010',
                    'master_schema_version' => 'master_schema_version_202412010',
                    'data_hash' => 'data_hash_202412010',
                    'server_db_hash' => 'server_db_hash_202412010',
                    'client_mst_data_hash' => 'client_mst_data_hash_202412010',
                    'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202412010',
                    'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202412010',
                    'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202412010',
                    'client_opr_data_hash' => 'client_opr_data_hash_202412010',
                    'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202412010',
                    'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202412010',
                    'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202412010',
                ],
            ],
            [
                'release_key' => 202411010,
                'description' => '2024年11月配信テスト',
                'enabled' => 1,
                'is_latest_version' => true,
                'is_end_release' => true,
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
        ], array_values($actual));
    }

    /**
     * @test
     */
    public function makeDiffData_比較データ生成(): void
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
        MngMasterReleaseVersion::factory()->create([
            'id' => '100',
            'release_key' => 202411010,
            'git_revision' => 'git_revision_202411010',
        ]);
        $mngMasterRelease2 = MngMasterRelease::factory()->create([
            // 配信準備中(インポートデータなし)
            'release_key' => 202412010,
            'enabled' => 0,
            'description' => '2024年12月配信テスト',
        ]);
        $fromEnvironment = 'test';
        // インポート元環境のマスターデータリリース情報
        $masterReleaseArrayFromEnvironment = [
            [
                'release_key' => 202412011,
                'description' => '2024年12月配信テスト修正版',
                'enabled' => 0,
                'is_latest_version' => false,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 202412011,
                    'git_revision' => 'git_revision_202412011',
                    'master_schema_version' => 'master_schema_version_202412011',
                    'data_hash' => 'data_hash_202412011',
                    'server_db_hash' => 'server_db_hash_202412011',
                    'client_mst_data_hash' => 'client_mst_data_hash_202412011',
                    'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202412011',
                    'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202412011',
                    'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202412011',
                    'client_opr_data_hash' => 'client_opr_data_hash_202412011',
                    'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202412011',
                    'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202412011',
                    'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202412011',
                ],
            ],
            [
                'release_key' => 202412010,
                'description' => '2024年12月配信テスト',
                'enabled' => 0,
                'is_latest_version' => false,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 202412010,
                    'git_revision' => 'git_revision_202412010',
                    'master_schema_version' => 'master_schema_version_202412010',
                    'data_hash' => 'data_hash_202412010',
                    'server_db_hash' => 'server_db_hash_202412010',
                    'client_mst_data_hash' => 'client_mst_data_hash_202412010',
                    'client_mst_data_i18n_ja_hash' => 'client_mst_data_i18n_ja_hash_202412010',
                    'client_mst_data_i18n_en_hash' => 'client_mst_data_i18n_en_hash_202412010',
                    'client_mst_data_i18n_zh_hash' => 'client_mst_data_i18n_zh_hash_202412010',
                    'client_opr_data_hash' => 'client_opr_data_hash_202412010',
                    'client_opr_data_i18n_ja_hash' => 'client_opr_data_i18n_ja_hash_202412010',
                    'client_opr_data_i18n_en_hash' => 'client_opr_data_i18n_en_hash_202412010',
                    'client_opr_data_i18n_zh_hash' => 'client_opr_data_i18n_zh_hash_202412010',
                ],
            ],
            [
                'release_key' => 202411010,
                'description' => '2024年11月配信テスト',
                'enabled' => 1,
                'is_latest_version' => true,
                'is_end_release' => true,
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
        ];

        // Exercise
        $importMngMasterRelease = new ImportMngMasterRelease();
        $importMngMasterRelease->mngMasterReleaseCollection = collect([
            $mngMasterRelease2,
            $mngMasterRelease1,
        ]);
        $importMngMasterRelease->masterReleaseArrayFromEnvironment = $masterReleaseArrayFromEnvironment;
        $actuals = $this->callMethod(
            $importMngMasterRelease,
            'makeDiffData',
            $fromEnvironment,
        );

        // Verify
        //  自環境とインポート元環境を比較したリリース情報が生成できているか
        //  自環境を軸にして表示するので、自環境で登録してないリリースキーの情報は表示されない
        $this->assertEquals([
            202412010 => [
                'self' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'git_revision_202412010',
                    'is_latest_version' => false,
                ],
            ],
            202411010 => [
                'self' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                ],
                'environment' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信終了',
                    'style' => '',
                    'git_revision' => 'git_revision_202411010',
                    'is_latest_version' => true,
                ],
            ],
        ], $actuals);
    }

    /**
     * @test
     */
    public function validationFromDiffEnvironments_異常なし_データが1件だけ(): void
    {
        // Setup
        $diffData = [
            [
                'self' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                ],
                'environment' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                    'is_latest_version' => true,
                ],
            ],
        ];

        // Exercise
        $importMngMasterRelease = new ImportMngMasterRelease();
        $this->callMethod(
            $importMngMasterRelease,
            'validationFromDiffEnvironments',
            $diffData,
        );

        // Verify
        // エラーメッセージが設定されてないこと
        $this->assertEquals($importMngMasterRelease->validationErrorMessage, '');
    }

    /**
     * @test
     */
    public function validationFromDiffEnvironments_異常なし_最大リリースキーのデータが空(): void
    {
        // Setup
        $diffData = [
            [
                'self' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [],
            ],
            [
                'self' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                ],
                'environment' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                    'is_latest_version' => true,
                ],
            ],
        ];

        // Exercise
        $importMngMasterRelease = new ImportMngMasterRelease();
        $this->callMethod(
            $importMngMasterRelease,
            'validationFromDiffEnvironments',
            $diffData,
        );

        // Verify
        // エラーメッセージが設定されてないこと
        $this->assertEquals($importMngMasterRelease->validationErrorMessage, '');
    }

    /**
     * @test
     */
    public function validationFromDiffEnvironments_異常なし_最大リリースキーのgit_revisionがない(): void
    {
        // Setup
        $diffData = [
            [
                'self' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                    'is_latest_version' => true,
                ],
            ],
            [
                'self' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                ],
                'environment' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                    'is_latest_version' => true,
                ],
            ],
        ];

        // Exercise
        $importMngMasterRelease = new ImportMngMasterRelease();
        $this->callMethod(
            $importMngMasterRelease,
            'validationFromDiffEnvironments',
            $diffData,
        );

        // Verify
        // エラーメッセージが設定されてないこと
        $this->assertEquals($importMngMasterRelease->validationErrorMessage, '');
    }

    /**
     * @test
     */
    public function validationFromDiffEnvironments_異常なし_件数が2件以上(): void
    {
        // Setup
        $diffData = [
            [
                'self' => [
                    'release_key' => 202412011,
                    'description' => '2024年12月配信テスト2',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [
                    'release_key' => 202412011,
                    'description' => '2024年12月配信テスト2',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'git_revision_202412010',
                    'is_latest_version' => true,
                ],
            ],
            [
                'self' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'git_revision_202412010',
                    'is_latest_version' => true,
                ],
            ],
            [
                'self' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                ],
                'environment' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                    'is_latest_version' => true,
                ],
            ],
        ];

        // Exercise
        $importMngMasterRelease = new ImportMngMasterRelease();
        $this->callMethod(
            $importMngMasterRelease,
            'validationFromDiffEnvironments',
            $diffData,
        );

        // Verify
        // エラーメッセージが設定されてないこと
        $this->assertEquals($importMngMasterRelease->validationErrorMessage, '');
    }

    /**
     * @test
     */
    public function validationFromDiffEnvironments_一致するリリースキー情報がない(): void
    {
        // Setup
        $diffData = [
            [
                'self' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [],
            ],
            [
                'self' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                ],
                'environment' => [],
            ],
        ];

        // Exercise
        $importMngMasterRelease = new ImportMngMasterRelease();
        $this->callMethod(
            $importMngMasterRelease,
            'validationFromDiffEnvironments',
            $diffData,
        );

        // Verify
        // エラーメッセージが設定されてないこと
        $this->assertEquals($importMngMasterRelease->validationErrorMessage, '一致するリリースキー情報がありません');
    }

    /**
     * @test
     */
    public function validationFromDiffEnvironments_git_revisionがない(): void
    {
        // Setup
        $diffData = [
            [
                'self' => [
                    'release_key' => 202501010,
                    'description' => '2025年1月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [
                    'release_key' => 202501010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                    'is_latest_version' => true,
                ],
            ],
            [
                'self' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                ],
                'environment' => [
                    'release_key' => 202412010,
                    'description' => '2024年12月配信テスト',
                    'status' => '配信準備中',
                    'style' => 'color: darkolivegreen',
                    'git_revision' => 'なし',
                    'is_latest_version' => true,
                ],
            ],
            [
                'self' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'git_revision_202411010',
                ],
                'environment' => [
                    'release_key' => 202411010,
                    'description' => '2024年11月配信テスト',
                    'status' => '配信中',
                    'style' => 'color: deeppink',
                    'git_revision' => 'なし',
                    'is_latest_version' => true,
                ],
            ],
        ];

        // Exercise
        $importMngMasterRelease = new ImportMngMasterRelease();
        $importMngMasterRelease->fromEnvironment = 'testDevelop';
        $this->callMethod(
            $importMngMasterRelease,
            'validationFromDiffEnvironments',
            $diffData,
        );

        // Verify
        // エラーメッセージが設定されていること
        $this->assertEquals($importMngMasterRelease->validationErrorMessage, 'testDevelopにgit_revisionが設定されていません');
    }
}
