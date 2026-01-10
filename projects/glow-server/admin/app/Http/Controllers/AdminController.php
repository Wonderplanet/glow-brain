<?php

namespace App\Http\Controllers;

use App\Services\AdmGachaCautionService;
use App\Services\AdmInformationService;
use App\Services\AdmS3ObjectService;
use App\Services\IgnService;
use App\Services\JumpPlusRewardService;
use App\Services\MessageDistributionService;
use App\Services\PromotionService;
use App\Traits\MngCacheDeleteTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Delegators\MasterAssetReleaseAdminDelegator;

/**
 * 管理ツール関連のAPIは、一旦このControllerに集約しつつ、もしも多くなった場合は分割する
 */
class AdminController extends Controller
{
    use MngCacheDeleteTrait;

    public function __construct(
        private readonly MasterAssetReleaseAdminDelegator $delegator,
        private readonly PromotionService $promotionService,
        private readonly AdmInformationService $admInformationService,
        private readonly IgnService $ignService,
        private readonly JumpPlusRewardService $jumpPlusRewardService,
        private readonly AdmGachaCautionService $admGachaCautionService,
        private readonly AdmS3ObjectService $admS3ObjectService,
        private readonly MessageDistributionService $messageDistributionService,
    ) {
    }

    /**
     * アセットの登録を行う
     * jenkinsビルドサーバーから呼ばれる
     * @param Request $request
     * @return JsonResponse
     */
    public function registerAsset(Request $request): JsonResponse
    {
        $response = $this->delegator->registerAsset($request);

        // キャッシュを削除する
        $this->deleteMngAssetReleaseVersionCache();

        return $response;
    }

    /**
     * 指定プラットフォームのアセットリリースキー一覧(配信ステータスが適用中もしくは準備中)を取得し返す
     * 他環境からアセットインポートを行う際に呼ばれるAPI
     * @param int $platform
     * @return JsonResponse
     */
    public function getEffectiveAssetReleases(int $platform): JsonResponse
    {
        return $this->delegator->getEffectiveAssetReleases($platform);
    }

    /**
     * 指定されたplatformとrelease_keyのmng_asset_releases, mng_asset_release_versions情報を取得して返す
     * 他環境からアセットインポートを行う際に呼ばれるAPI
     * @param int $platform
     * @param int|null $releaseKey
     * @return JsonResponse
     */
    public function getAssetReleaseData(int $platform, int|null $releaseKey): JsonResponse
    {
        return $this->delegator->getAssetReleaseData($platform, $releaseKey);
    }

    /**
     * 配信中と配信準備中(mng_master_release_versionあり)のデータを取得する
     * マスターデータ環境間インポートを行う際に呼ばれるAPI
     *
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getMasterReleaseData(Request $request): JsonResponse
    {
        ini_set('max_execution_time', 900); // 10分間実行可能にする
        $releaseKeys = $request->query('releaseKeys', []);
        return $this->delegator->getMasterReleaseData($releaseKeys);
    }

    public function getPromotionTag(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $result = $this->promotionService->getPromotionTag($limit);

        return response()->json($result);
    }

    public function getInformationPromotionData(string $admPromotionTagId): JsonResponse
    {
        $result = $this->admInformationService->getInformationPromotionData($admPromotionTagId);

        return response()->json($result);
    }

    public function getTagPromotionData(string $admPromotionTagId): JsonResponse
    {
        $result = $this->promotionService->getTagPromotionData($admPromotionTagId);

        return response()->json($result);
    }

    public function getIgnPromotionData(string $admPromotionTagId): JsonResponse
    {
        $result = $this->ignService->getIgnPromotionData($admPromotionTagId);

        return response()->json($result);
    }

    public function getJumpPlusRewardPromotionData(string $admPromotionTagId): JsonResponse
    {
        $result = $this->jumpPlusRewardService->getJumpPlusRewardPromotionData($admPromotionTagId);

        return response()->json($result);
    }

    public function getGachaCautionPromotionData(string $admPromotionTagId): JsonResponse
    {
        $result = $this->admGachaCautionService->getGachaCautionPromotionData($admPromotionTagId);

        return response()->json($result);
    }

    public function getS3ObjectPromotionData(string $admPromotionTagId): JsonResponse
    {
        $result = $this->admS3ObjectService->getS3ObjectPromotionData($admPromotionTagId);

        return response()->json($result);
    }

    public function getMessageDistributionPromotionData(string $admPromotionTagId): JsonResponse
    {
        $result = $this->messageDistributionService->getMessageDistributionPromotionData($admPromotionTagId);

        return response()->json($result);
    }
}
