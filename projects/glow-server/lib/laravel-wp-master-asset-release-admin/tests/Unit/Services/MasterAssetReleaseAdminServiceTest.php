<?php

namespace MasterAssetReleaseAdmin\Unit\Services;

use Illuminate\Http\Request;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmAssetImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MasterAssetReleaseAdminService;
use WonderPlanet\Tests\TestCase;

class MasterAssetReleaseAdminServiceTest extends TestCase
{
    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;
    
    private MasterAssetReleaseAdminService $masterAssetReleaseAdminService;

    public function setUp(): void
    {
        parent::setUp();

        $this->masterAssetReleaseAdminService = app(MasterAssetReleaseAdminService::class);
    }

    /**
     * @test
     */
    public function registerAsset_データなし_リリースフラグON()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 20240401,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            'update_target_release_version' => true,
        ];

        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::query()->get();
        $this->assertEquals(1, $mngAssetReleaseList->count());
        $mngAssetRelease = $mngAssetReleaseList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetRelease->release_key);
        $this->assertEquals($platform, $mngAssetRelease->platform);
        $this->assertFalse($mngAssetRelease->enabled);
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->target_release_version_id);
        $this->assertNull($mngAssetRelease->description);
        $this->assertNotEmpty($mngAssetRelease->created_at);
        $this->assertNotEmpty($mngAssetRelease->updated_at);
    }

    /**
     * @test
     */
    public function registerAsset_データなし_リリースフラグOFF()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 20240401,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            // falseなので即時リリースされない
            'update_target_release_version' => false,
        ];

        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::query()->get();
        $this->assertEquals(1, $mngAssetReleaseList->count());
        $mngAssetRelease = $mngAssetReleaseList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetRelease->release_key);
        $this->assertEquals($platform, $mngAssetRelease->platform);
        $this->assertFalse($mngAssetRelease->enabled);
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->target_release_version_id);
        $this->assertNull($mngAssetRelease->description);
        $this->assertNotEmpty($mngAssetRelease->created_at);
        $this->assertNotEmpty($mngAssetRelease->updated_at);
    }

    /**
     * @test
     */
    public function registerAsset_データあり_配信準備中_リリースフラグON()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 2024112801,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            'update_target_release_version' => true,
        ];

        $mngAssetReleaseTestData = $this->getMngAssetReleaseTestDataForStatusTest(PlatformConstant::PLATFORM_ANDROID);
        foreach ($mngAssetReleaseTestData as $data) {
            MngAssetRelease::create($data);
        }

        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::all()
            ->map(fn (MngAssetRelease $model) => $model->toEntity());
        $this->assertEquals(5, $mngAssetReleaseList->count());

        // target_release_version_idが更新されているかチェック
        $mngAssetRelease = $mngAssetReleaseList
            ->first(fn ($entity) => $entity->getReleaseKey() === 2024112801);
        $this->assertEquals(2024112801, $mngAssetRelease->getReleaseKey());
        $this->assertEquals($platform, $mngAssetRelease->getPlatform());
        $this->assertEquals(false, $mngAssetRelease->getEnabled());
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->getTargetReleaseVersionId());
    }
    
    /**
     * @test
     */
    public function registerAsset_データあり_配信終了_リリースフラグON()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 2024112601,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            'update_target_release_version' => true,
        ];

        $mngAssetReleaseTestData = $this->getMngAssetReleaseTestDataForStatusTest(PlatformConstant::PLATFORM_ANDROID);
        foreach ($mngAssetReleaseTestData as $data) {
            MngAssetRelease::create($data);
        }
        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::all()
            ->map(fn (MngAssetRelease $model) => $model->toEntity());
        $this->assertEquals(5, $mngAssetReleaseList->count());

        // target_release_version_idが更新されているかチェック
        $mngAssetRelease = $mngAssetReleaseList
            ->first(fn ($entity) => $entity->getReleaseKey() === 2024112601);
        $this->assertEquals(2024112601, $mngAssetRelease->getReleaseKey());
        $this->assertEquals($platform, $mngAssetRelease->getPlatform());
        $this->assertEquals(true, $mngAssetRelease->getEnabled());
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->getTargetReleaseVersionId());
    }

    /**
     * @test
     */
    public function registerAsset_データあり_配信中_リリースフラグOFF()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 2024112701,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            'update_target_release_version' => false,
        ];

        $mngAssetReleaseTestData = $this->getMngAssetReleaseTestDataForStatusTest(PlatformConstant::PLATFORM_ANDROID);
        foreach ($mngAssetReleaseTestData as $data) {
            MngAssetRelease::create($data);
        }

        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::all()
            ->map(fn (MngAssetRelease $model) => $model->toEntity());
        $this->assertEquals(5, $mngAssetReleaseList->count());

        // target_release_version_idが更新されていないことを確認
        $mngAssetRelease = $mngAssetReleaseList
            ->first(fn ($entity) => $entity->getReleaseKey() === 2024112701);
        $this->assertEquals(2024112701, $mngAssetRelease->getReleaseKey());
        $this->assertEquals($platform, $mngAssetRelease->getPlatform());
        $this->assertEquals(true, $mngAssetRelease->getEnabled());

        /**
         * ペンディング機能を廃止し、即時配信のみに変更したため、こちらのテストも合わせて変更する
         * 参考：https://github.com/Wonderplanet/laravel-wp-framework/issues/1136
         */
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->getTargetReleaseVersionId());
        $this->assertNotEquals(1, $mngAssetRelease->getTargetReleaseVersionId());
    }

    /**
     * @test
     */
    public function registerAsset_データあり_配信準備中_リリースフラグOFF()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 2024112801,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            'update_target_release_version' => false,
        ];

        $mngAssetReleaseTestData = $this->getMngAssetReleaseTestDataForStatusTest(PlatformConstant::PLATFORM_ANDROID);
        foreach ($mngAssetReleaseTestData as $data) {
            MngAssetRelease::create($data);
        }
        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::all()
            ->map(fn (MngAssetRelease $model) => $model->toEntity());
        $this->assertEquals(5, $mngAssetReleaseList->count());

        // target_release_version_idが更新されていることを確認
        $mngAssetRelease = $mngAssetReleaseList
            ->first(fn ($entity) => $entity->getReleaseKey() === 2024112801);
        $this->assertEquals(2024112801, $mngAssetRelease->getReleaseKey());
        $this->assertEquals($platform, $mngAssetRelease->getPlatform());
        $this->assertEquals(false, $mngAssetRelease->getEnabled());
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->getTargetReleaseVersionId());
    }

    /**
     * @test
     */
    public function registerAsset_データあり_配信終了_リリースフラグOFF()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 2024112602,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            'update_target_release_version' => false,
        ];

        $mngAssetReleaseTestData = $this->getMngAssetReleaseTestDataForStatusTest(PlatformConstant::PLATFORM_ANDROID);
        foreach ($mngAssetReleaseTestData as $data) {
            MngAssetRelease::create($data);
        }
        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::all()
            ->map(fn (MngAssetRelease $model) => $model->toEntity());
        $this->assertEquals(5, $mngAssetReleaseList->count());

        // target_release_version_idが更新されていることを確認
        $mngAssetRelease = $mngAssetReleaseList
            ->first(fn ($entity) => $entity->getReleaseKey() === 2024112602);
        $this->assertEquals(2024112602, $mngAssetRelease->getReleaseKey());
        $this->assertEquals($platform, $mngAssetRelease->getPlatform());
        $this->assertEquals(true, $mngAssetRelease->getEnabled());
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->getTargetReleaseVersionId());
    }
    
    /**
     * MngAssetReleaseのテストデータ取得
     * 配信ステータステスト用
     * @return array<array, array>
     */
    private function getMngAssetReleaseTestDataForStatusTest(int $platform): array
    {
        return [
            [
                // 最新リリースキー、配信中
                'release_key' => 2024112701,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => 1,
                'description' => "配信中memo",
            ],
            [
                // 配信終了
                'release_key' => 2024112601,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => 1,
                'description' => "配信終了",
            ],
            [
                // 配信準備中
                'release_key' => 2024112801,
                'platform' => $platform,
                'enabled' => false,
                'target_release_version_id' => 1,
                'description' => "配信準備中",
            ],
            [
                // 配信準備中
                'release_key' => 2024112802,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => null,
                'description' => "配信準備中2",
            ],
            [
                // 配信終了
                'release_key' => 2024112602,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => null,
                'description' => "配信終了しているデータ",
            ],
        ];
    }

    /**
     * @test
     */
    public function registerAsset_ログ挿入確認()
    {
        // Setup
        $param = [
            'mng_asset_release_versions' => [
                'release_key' => 20240401,
                'git_revision' => '289368bc',
                'git_branch' => 'v1.2.0/20240401',
                'catalog_hash' => '5c8e3cc5/1',
                'platform' => 'Android',
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 124048576,
                'catalog_byte_size' => 245356,
                'catalog_file_name' => 'catalog_1.data',
                'catalog_hash_file_name' => 'catalog_1.hash',
            ],
            'update_target_release_version' => true,
        ];
        $request = Request::create(
            'register-asset',
            'POST',
            $param
        );

        // Exercise
        $this->masterAssetReleaseAdminService->registerAsset($request);

        // Verify
        $paramMngAssetReleaseVersion = $param['mng_asset_release_versions'];
        $platform = array_search($paramMngAssetReleaseVersion['platform'], PlatformConstant::PLATFORM_STRING_LIST);

        // mng_asset_release_versionsのレコード内容チェック
        $mngAssetReleaseVersionList = MngAssetReleaseVersion::all()->map(function (MngAssetReleaseVersion $model) {
            return $model->toEntity();
        });
        $this->assertEquals(1, $mngAssetReleaseVersionList->count());
        $mngAssetReleaseVersion = $mngAssetReleaseVersionList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetReleaseVersion->getReleaseKey());
        $this->assertEquals($paramMngAssetReleaseVersion['git_revision'], $mngAssetReleaseVersion->getGitRevision());
        $this->assertEquals($paramMngAssetReleaseVersion['git_branch'], $mngAssetReleaseVersion->getGitBranch());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash'], $mngAssetReleaseVersion->getCatalogHash());
        $this->assertEquals($platform, $mngAssetReleaseVersion->getPlatform());
        $this->assertEquals($paramMngAssetReleaseVersion['build_client_version'], $mngAssetReleaseVersion->getBuildClientVersion());
        $this->assertEquals($paramMngAssetReleaseVersion['asset_total_byte_size'], $mngAssetReleaseVersion->getAssetTotalByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_byte_size'], $mngAssetReleaseVersion->getCatalogByteSize());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_file_name'], $mngAssetReleaseVersion->getCatalogFileName());
        $this->assertEquals($paramMngAssetReleaseVersion['catalog_hash_file_name'], $mngAssetReleaseVersion->getCatalogHashFileName());

        // mng_asset_releasesのレコード内容チェック
        $mngAssetReleaseList = MngAssetRelease::query()->get();
        $this->assertEquals(1, $mngAssetReleaseList->count());
        $mngAssetRelease = $mngAssetReleaseList->first();
        $this->assertEquals($paramMngAssetReleaseVersion['release_key'], $mngAssetRelease->release_key);
        $this->assertEquals($platform, $mngAssetRelease->platform);
        $this->assertFalse($mngAssetRelease->enabled);
        $this->assertEquals($mngAssetReleaseVersion->getId(), $mngAssetRelease->target_release_version_id);
        $this->assertNull($mngAssetRelease->description);
        $this->assertNotEmpty($mngAssetRelease->created_at);
        $this->assertNotEmpty($mngAssetRelease->updated_at);

        // adm_asset_import_historiesにログが挿入されているか調べる
        $admAssetImportHistory = AdmAssetImportHistory::query()->get();
        $this->assertCount(1, $admAssetImportHistory);
        $admAssetImportHistory = $admAssetImportHistory->first();
        $this->assertEquals($mngAssetReleaseVersion->getId(), $admAssetImportHistory->mng_asset_release_version_id);
        $this->assertEquals('register-asset-api', $admAssetImportHistory->import_adm_user_id);
        $this->assertEquals('register-asset', $admAssetImportHistory->import_source);
        $this->assertNotEmpty($admAssetImportHistory->created_at);
        $this->assertNotEmpty($admAssetImportHistory->updated_at);
    }

    /**
     * @test
     * @dataProvider getAssetReleaseData
     */
    public function getAssetReleaseData_アセットバージョンデータ取得(int $platform)
    {
        // Setup
        MngAssetRelease::factory()->createMany([
            [
                // 配信中(最新)
                'release_key' => 2024112701,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => 1,
                'description' => "配信中memo",
            ],
            [
                // 配信中(最古)
                'release_key' => 2024112602,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => 4,
                'description' => "配信終了しているデータ",
            ],
            [
                // 配信準備中
                'release_key' => 2024112801,
                'platform' => $platform,
                'enabled' => false,
                'target_release_version_id' => 3,
                'description' => "配信準備中",
            ],
            [
                // 配信準備中
                'release_key' => 2024112802,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => null,
                'description' => "配信準備中2",
            ],
            [
                // 配信終了
                'release_key' => 2024112601,
                'platform' => $platform,
                'enabled' => true,
                'target_release_version_id' => 2,
                'description' => "配信終了memo",
            ],
        ]);
        MngAssetReleaseVersion::factory()->createMany([
            [
                // 配信中(最新)
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
                // 配信終了
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
                // 配信準備中
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
            [
                // 配信中(最古)
                'id' => 4,
                'release_key' => 2024112602,
                'platform' => $platform,
                'catalog_hash' => "test_hash4",
                'git_revision' => "test",
                'git_branch' => "test",
                'build_client_version' => "test",
                'asset_total_byte_size' => 100,
                'catalog_byte_size' => 100,
                'catalog_file_name' => "test",
                'catalog_hash_file_name' => "test",
            ],
        ]);
        $targetReleaseKey = 2024112701;

        // Exercise
        $response = $this->masterAssetReleaseAdminService->getAssetReleaseData($platform, $targetReleaseKey);

        // Verify
        $contentJson = $response->getContent();
        $actualData = json_decode($contentJson, true);
        $actualAssetReleaseInfo = $actualData['asset_release_info'];
        $this->assertNotEmpty($actualAssetReleaseInfo);
        $this->assertEquals($targetReleaseKey, $actualAssetReleaseInfo['release_key']);
        $this->assertEquals("test_hash1", $actualAssetReleaseInfo['catalog_hash']);
        $this->assertEquals("配信中memo", $actualAssetReleaseInfo['description']);
        $actualMngAssetVersion = $actualData['mng_asset_release_version'];
        $this->assertNotEmpty($actualMngAssetVersion);
        $this->assertEquals($targetReleaseKey, $actualMngAssetVersion['release_key']);
        $this->assertEquals("test_hash1", $actualMngAssetVersion['catalog_hash']);
    }

    /**
     * @return array[]
     */
    private function getAssetReleaseData(): array
    {
        return [
            'ios' => [PlatformConstant::PLATFORM_IOS],
            'android' => [PlatformConstant::PLATFORM_IOS],
        ];
    }

    /**
     * @test
     */
    public function getMasterReleaseData_データ取得チェック(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                // 配信終了
                'id' => 'master-0',
                'release_key' => 2024083101,
                'enabled' => 1,
                'target_release_version_id' => '100',
                'description' => '2024年8月配信版'
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信中(最古)
                'id' => 'master-1',
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '101',
                'description' => '2024年9月配信版(最古)'
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信中(最新)
                'id' => 'master-2',
                'release_key' => 2024090102,
                'enabled' => 1,
                'target_release_version_id' => '102',
                'description' => '2024年9月配信版(最新)'
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-3',
                'release_key' => 2024090103,
                'enabled' => 0,
                'target_release_version_id' => '103',
                'description' => '2024年9月修正版'
            ]);
        MngMasterRelease::factory()
            ->create([
                // 配信準備中 設定なし
                'id' => 'master-4',
                'release_key' => 2024090104,
                'enabled' => 0,
            ]);
        $version0 = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '100',
                'release_key' => 2024083101,
            ]);
        $version1 = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090101,
            ]);
        $version2 = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090102,
            ]);
        $version3 = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '103',
                'release_key' => 2024090103,
            ]);
        AdmMasterImportHistoryVersion::factory()->createMany([
            [
                'mng_master_release_version_id' => '100',
                'created_at' => '2024-08-01 00:00:00',
            ],
            [
                'mng_master_release_version_id' => '101',
                'created_at' => '2024-08-31 00:00:00',
            ],
            [
                'mng_master_release_version_id' => '102',
                'created_at' => '2024-09-02 00:00:00',
            ],
            [
                'mng_master_release_version_id' => '103',
                'created_at' => '2024-09-03 00:00:00',
            ]
        ]);

        // Exercise
        $response = $this->masterAssetReleaseAdminService->getMasterReleaseData([2024083101, 2024090101, 2024090102, 2024090103]);

        // Verify
        $contentJson = $response->getContent();
        $actualData = json_decode($contentJson, true);

        // 指定したリリースキーのデータが取得できているか
        $this->assertCount(4, $actualData);
        $this->assertEquals([
            [
                'release_key' => 2024083101,
                'description' => '2024年8月配信版',
                'enabled' => 1,
                'is_latest_version' => true,
                'is_end_release' => true,
                'mng_master_release_versions' => [
                    'release_key' => 2024083101,
                    'git_revision' => $version0->git_revision,
                    'master_schema_version' => $version0->master_schema_version,
                    'data_hash' => $version0->data_hash,
                    'server_db_hash' => $version0->server_db_hash,
                    'client_mst_data_hash' => $version0->client_mst_data_hash,
                    'client_mst_data_i18n_ja_hash' => $version0->client_mst_data_i18n_ja_hash,
                    'client_mst_data_i18n_en_hash' => $version0->client_mst_data_i18n_en_hash,
                    'client_mst_data_i18n_zh_hash' => $version0->client_mst_data_i18n_zh_hash,
                    'client_opr_data_hash' => $version0->client_opr_data_hash,
                    'client_opr_data_i18n_ja_hash' => $version0->client_opr_data_i18n_ja_hash,
                    'client_opr_data_i18n_en_hash' => $version0->client_opr_data_i18n_en_hash,
                    'client_opr_data_i18n_zh_hash' => $version0->client_opr_data_i18n_zh_hash,
                ]
            ],
            [
                'release_key' => 2024090101,
                'description' => '2024年9月配信版(最古)',
                'enabled' => 1,
                'is_latest_version' => true,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 2024090101,
                    'git_revision' => $version1->git_revision,
                    'master_schema_version' => $version1->master_schema_version,
                    'data_hash' => $version1->data_hash,
                    'server_db_hash' => $version1->server_db_hash,
                    'client_mst_data_hash' => $version1->client_mst_data_hash,
                    'client_mst_data_i18n_ja_hash' => $version1->client_mst_data_i18n_ja_hash,
                    'client_mst_data_i18n_en_hash' => $version1->client_mst_data_i18n_en_hash,
                    'client_mst_data_i18n_zh_hash' => $version1->client_mst_data_i18n_zh_hash,
                    'client_opr_data_hash' => $version1->client_opr_data_hash,
                    'client_opr_data_i18n_ja_hash' => $version1->client_opr_data_i18n_ja_hash,
                    'client_opr_data_i18n_en_hash' => $version1->client_opr_data_i18n_en_hash,
                    'client_opr_data_i18n_zh_hash' => $version1->client_opr_data_i18n_zh_hash,
                ]
            ],
            [
                'release_key' => 2024090102,
                'description' => '2024年9月配信版(最新)',
                'enabled' => 1,
                'is_latest_version' => true,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 2024090102,
                    'git_revision' => $version2->git_revision,
                    'master_schema_version' => $version2->master_schema_version,
                    'data_hash' => $version2->data_hash,
                    'server_db_hash' => $version2->server_db_hash,
                    'client_mst_data_hash' => $version2->client_mst_data_hash,
                    'client_mst_data_i18n_ja_hash' => $version2->client_mst_data_i18n_ja_hash,
                    'client_mst_data_i18n_en_hash' => $version2->client_mst_data_i18n_en_hash,
                    'client_mst_data_i18n_zh_hash' => $version2->client_mst_data_i18n_zh_hash,
                    'client_opr_data_hash' => $version2->client_opr_data_hash,
                    'client_opr_data_i18n_ja_hash' => $version2->client_opr_data_i18n_ja_hash,
                    'client_opr_data_i18n_en_hash' => $version2->client_opr_data_i18n_en_hash,
                    'client_opr_data_i18n_zh_hash' => $version2->client_opr_data_i18n_zh_hash,
                ]
            ],
            [
                'release_key' => 2024090103,
                'description' => '2024年9月修正版',
                'enabled' => 0,
                'is_latest_version' => true,
                'is_end_release' => false,
                'mng_master_release_versions' => [
                    'release_key' => 2024090103,
                    'git_revision' => $version3->git_revision,
                    'master_schema_version' => $version3->master_schema_version,
                    'data_hash' => $version3->data_hash,
                    'server_db_hash' => $version3->server_db_hash,
                    'client_mst_data_hash' => $version3->client_mst_data_hash,
                    'client_mst_data_i18n_ja_hash' => $version3->client_mst_data_i18n_ja_hash,
                    'client_mst_data_i18n_en_hash' => $version3->client_mst_data_i18n_en_hash,
                    'client_mst_data_i18n_zh_hash' => $version3->client_mst_data_i18n_zh_hash,
                    'client_opr_data_hash' => $version3->client_opr_data_hash,
                    'client_opr_data_i18n_ja_hash' => $version3->client_opr_data_i18n_ja_hash,
                    'client_opr_data_i18n_en_hash' => $version3->client_opr_data_i18n_en_hash,
                    'client_opr_data_i18n_zh_hash' => $version3->client_opr_data_i18n_zh_hash,
                ]
            ],
        ], array_values($actualData));
    }
}
