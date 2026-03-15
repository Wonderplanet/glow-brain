<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Delegators;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MasterAssetReleaseAdminService;

readonly class MasterAssetReleaseAdminDelegator
{
    public function __construct(
        private readonly MasterAssetReleaseAdminService $masterAssetReleaseAdminService
    ) {
    }

    /**
     * アセットの登録を行う
     * jenkinsビルドサーバーから呼ばれる
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerAsset(Request $request): JsonResponse
    {
        return $this->masterAssetReleaseAdminService->registerAsset($request);
    }

    /**
     * 指定プラットフォームのアセットリリースキー一覧(配信ステータスが適用中もしくは準備中)を取得し返す
     * 他環境からアセットインポートを行う際に呼ばれるAPI
     *
     * @param int $platform
     * @return JsonResponse
     */
    public function getEffectiveAssetReleases(int $platform): JsonResponse
    {
        return $this->masterAssetReleaseAdminService->getEffectiveAssetReleases($platform);
    }

    /**
     * 指定されたplatformとrelease_keyのmng_asset_releases, mng_asset_release_versions情報を取得して返す
     * 他環境からアセットインポートを行う際に呼ばれるAPI
     *
     * @param int $platform
     * @param int|null $releaseKey
     * @return JsonResponse
     */
    public function getAssetReleaseData(int $platform, int|null $releaseKey): JsonResponse
    {
        return $this->masterAssetReleaseAdminService->getAssetReleaseData($platform, $releaseKey);
    }
    
    /**
     * 配信中と配信準備中(mng_master_release_versionあり)のデータを取得する
     * マスターデータ環境間インポートを行う際に呼ばれるAPI
     *
     * @param array $releaseKeys
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getMasterReleaseData(array $releaseKeys): JsonResponse
    {
        return $this->masterAssetReleaseAdminService->getMasterReleaseData($releaseKeys);
    }
}
