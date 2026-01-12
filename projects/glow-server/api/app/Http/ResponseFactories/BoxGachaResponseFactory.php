<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Domain\BoxGacha\Models\UsrBoxGachaInterface;
use App\Domain\Resource\Entities\Rewards\BoxGachaReward;
use App\Http\Responses\ResultData\BoxGachaDrawResultData;
use App\Http\Responses\ResultData\BoxGachaInfoResultData;
use App\Http\Responses\ResultData\BoxGachaResetResultData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class BoxGachaResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    /**
     * BOXガチャ情報取得レスポンス
     *
     * @param BoxGachaInfoResultData $resultData
     * @return JsonResponse
     */
    public function createInfoResponse(BoxGachaInfoResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->addUsrBoxGachaData($result, $resultData->usrBoxGacha);

        return response()->json($result);
    }

    /**
     * BOXガチャ抽選レスポンス
     *
     * @param BoxGachaDrawResultData $resultData
     * @return JsonResponse
     */
    public function createDrawResponse(BoxGachaDrawResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);
        $result = $this->responseDataFactory->addUsrArtworkData($result, $resultData->usrArtworks);
        $result = $this->responseDataFactory->addUsrArtworkFragmentData($result, $resultData->usrArtworkFragments);
        $result = $this->addUsrBoxGachaData($result, $resultData->usrBoxGacha);
        $result = $this->addBoxGachaRewardData($result, $resultData->boxGachaRewards);

        return response()->json($result);
    }

    /**
     * BOXガチャリセットレスポンス
     *
     * @param BoxGachaResetResultData $resultData
     * @return JsonResponse
     */
    public function createResetResponse(BoxGachaResetResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->addUsrBoxGachaData($result, $resultData->usrBoxGacha);

        return response()->json($result);
    }

    /**
     * usrBoxGachaデータをレスポンスに追加
     *
     * @param array<mixed> $result
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @return array<mixed>
     */
    private function addUsrBoxGachaData(array $result, UsrBoxGachaInterface $usrBoxGacha): array
    {
        // draw_prizesを配列形式に変換（クライアント向け）
        $drawPrizes = $usrBoxGacha->getDrawPrizes()->map(
            fn(int $count, string $mstBoxGachaPrizeId) => [
                'mstBoxGachaPrizeId' => $mstBoxGachaPrizeId,
                'count' => $count,
            ]
        )->values()->toArray();

        $result['usrBoxGacha'] = [
            'mstBoxGachaId' => $usrBoxGacha->getMstBoxGachaId(),
            'resetCount' => $usrBoxGacha->getResetCount(),
            'totalDrawCount' => $usrBoxGacha->getTotalDrawCount(),
            'currentBoxLevel' => $usrBoxGacha->getCurrentBoxLevel(),
            'drawPrizes' => $drawPrizes,
        ];

        return $result;
    }

    /**
     * BOXガチャ報酬データをレスポンスに追加
     *
     * @param array<mixed> $result
     * @param Collection<BoxGachaReward> $rewards
     * @return array<mixed>
     */
    private function addBoxGachaRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var BoxGachaReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['boxGachaRewards'] = $response;

        return $result;
    }
}
