<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Domain\Common\Utils\StringUtil;
use App\Http\Responses\Data\GameFetchData;
use App\Http\Responses\ResultData\GameBadgeResultData;
use App\Http\Responses\ResultData\GameFetchResultData;
use App\Http\Responses\ResultData\GameServerTimeResultData;
use App\Http\Responses\ResultData\GameUpdateAndFetchResultData;
use App\Http\Responses\ResultData\GameVersionResultData;
use Illuminate\Http\JsonResponse;

class GameResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createVersionResponse(GameVersionResultData $resultData): JsonResponse
    {
        $result = [];

        $result['mstHash'] = $resultData->mstHash;
        $result['oprHash'] = $resultData->oprHash;
        $result['mstI18nHash'] = $resultData->mstI18nHash;
        $result['oprI18nHash'] = $resultData->oprI18nHash;
        $result['mstPath'] = $resultData->mstPath;
        $result['oprPath'] = $resultData->oprPath;
        $result['mstI18nPath'] = $resultData->mstI18nPath;
        $result['oprI18nPath'] = $resultData->oprI18nPath;
        $result['assetCatalogDataPath'] = $resultData->assetCatalogDataPath;
        $result['assetHash'] = $resultData->assetHash;
        $result['tosVersion'] = $resultData->tosVersion;
        $result['tosUserAgreeVersion'] = $resultData->tosUserAgreeVersion;
        $result['tosUrl'] = $resultData->tosUrl;
        $result['privacyPolicyVersion'] = $resultData->privacyPolicyVersion;
        $result['privacyPolicyUserAgreeVersion'] = $resultData->privacyPolicyUserAgreeVersion;
        $result['privacyPolicyUrl'] = $resultData->privacyPolicyUrl;
        $result['globalCnsntVersion'] = $resultData->globalCnsntVersion;
        $result['globalCnsntUserAgreeVersion'] = $resultData->globalCnsntUserAgreeVersion;
        $result['globalCnsntUrl'] = $resultData->globalCnsntUrl;
        $result['inAppAdvertisementVersion'] = $resultData->iaaVersion;
        $result['inAppAdvertisementUserAgreeVersion'] = $resultData->iaaUserAgreeVersion;
        $result['inAppAdvertisementUrl'] = $resultData->iaaUrl;

        return response()->json($result);
    }

    /**
     * @param array<mixed> $result
     * @param GameFetchData $gameFetchData
     * @return array<mixed>
     */
    private function addFetchResponse(array $result, GameFetchData $gameFetchData): array
    {
        $response = [];
        $response = $this->responseDataFactory->addUsrParameterData($response, $gameFetchData->usrUserParameter);
        $response = $this->responseDataFactory->addUsrBuyCountData($response, $gameFetchData->usrUserBuyCount);
        $response = $this->responseDataFactory->addUsrStageData($response, $gameFetchData->usrStages, true);
        $response = $this->responseDataFactory->addUsrStageEventData($response, $gameFetchData->usrStageEvents, true);
        $response = $this->responseDataFactory->addUsrStageEnhanceData(
            $response,
            $gameFetchData->usrStageEnhanceStatusDataList,
            true,
        );
        $response = $this->responseDataFactory->addUsrAdventBattleData(
            $response,
            $gameFetchData->usrAdventBattles,
            true
        );
        $response = $this->responseDataFactory->addGameBadgeData($response, $gameFetchData->gameBadgeData);
        $response = $this->responseDataFactory->addMissionStatusData($response, $gameFetchData->missionStatusData);

        $result['fetch'] = $response;

        return $result;
    }

    public function createUpdateAndFetchResponse(GameUpdateAndFetchResultData $resultData): JsonResponse
    {
        $result = [];

        $gameFetchOtherData = $resultData->gameFetchOtherData;
        $gameUpdateData = $resultData->gameUpdateData;

        $fetchOther = [];
        $fetchOther = $this->responseDataFactory->addTutorialStatusData($fetchOther, $gameFetchOtherData->usrUser->getTutorialStatus());
        $fetchOther = $this->responseDataFactory->addUsrProfileData($fetchOther, $gameFetchOtherData->usrUserProfile);
        $fetchOther = $this->responseDataFactory->addUsrItemData($fetchOther, $gameFetchOtherData->usrItems, true);
        $fetchOther = $this->responseDataFactory->addUsrUnitData($fetchOther, $gameFetchOtherData->usrUnits, true);
        $fetchOther = $this->responseDataFactory->addUsrStoreProductsData(
            $fetchOther,
            $gameFetchOtherData->oprProducts,
            $gameFetchOtherData->usrStoreProducts
        );
        $fetchOther = $this->responseDataFactory->addUsrShopItemsData(
            $fetchOther,
            $gameFetchOtherData->usrShopItems
        );
        $fetchOther = $this->responseDataFactory->addUsrTradePackData(
            $fetchOther,
            $gameFetchOtherData->usrTradePacks
        );
        $fetchOther = $this->responseDataFactory->addUsrConditionPackData(
            $fetchOther,
            $gameFetchOtherData->usrConditionPacks
        );
        $fetchOther = $this->responseDataFactory->addUsrIdleIncentiveData(
            $fetchOther,
            $gameFetchOtherData->usrIdleIncentive
        );
        $fetchOther = $this->responseDataFactory->addUsrPartyData($fetchOther, $gameFetchOtherData->usrParties, true);
        $fetchOther = $this->responseDataFactory->addUsrOutpostData(
            $fetchOther,
            $gameFetchOtherData->usrOutposts,
            true
        );
        $fetchOther = $this->responseDataFactory->addUsrOutpostEnhancementData(
            $fetchOther,
            $gameFetchOtherData->usrOutpostEnhancements,
        );
        $fetchOther = $this->responseDataFactory->addUsrInGameStatusData(
            $fetchOther,
            $gameFetchOtherData->usrInGameStatus
        );
        $fetchOther = $this->responseDataFactory->addUsrLoginData($fetchOther, $gameFetchOtherData->usrUserLogin);
        $fetchOther = $this->responseDataFactory->addMissionDailyBonusRewardData(
            $fetchOther,
            $gameFetchOtherData->missionDailyBonusRewards,
        );
        $fetchOther = $this->responseDataFactory->addMissionEventDailyBonusRewardData(
            $fetchOther,
            $gameFetchOtherData->missionEventDailyBonusRewards,
        );
        $fetchOther = $this->responseDataFactory->addUsrEventDailyBonusProgressData(
            $fetchOther,
            $gameFetchOtherData->usrEventDailyBonusProgresses,
        );
        $fetchOther = $this->responseDataFactory->addComebackBonusRewardData(
            $fetchOther,
            $gameFetchOtherData->comebackBonusRewards,
        );
        $fetchOther = $this->responseDataFactory->addUsrComebackBonusProgressData(
            $fetchOther,
            $gameFetchOtherData->usrComebackBonusProgresses,
        );
        $fetchOther = $this->responseDataFactory->addUsrEmblemData($fetchOther, $gameFetchOtherData->usrEmblems);
        $fetchOther = $this->responseDataFactory->addUsrArtworkData($fetchOther, $gameFetchOtherData->usrArtworks);
        $fetchOther = $this->responseDataFactory->addUsrArtworkFragmentData(
            $fetchOther,
            $gameFetchOtherData->usrArtworkFragments
        );
        $fetchOther = $this->responseDataFactory->addUsrReceivedUnitEncyclopediaRewardData(
            $fetchOther,
            $gameFetchOtherData->usrReceivedUnitEncyclopediaRewards
        );
        $fetchOther = $this->responseDataFactory->addMngInGameNoticeData(
            $fetchOther,
            $gameFetchOtherData->mngInGameNoticeDataList
        );
        $fetchOther = $this->responseDataFactory->addUsrStoreInfoData($fetchOther, $gameFetchOtherData->usrStoreInfoData);
        $fetchOther = $this->responseDataFactory->addOprCampaignData(
            $fetchOther,
            $gameFetchOtherData->oprCampaignDataList
        );
        $fetchOther = $this->responseDataFactory->addUsrGachaUpperData($fetchOther, $gameFetchOtherData->usrGachaUppers, true);
        $fetchOther = $this->responseDataFactory->addUsrGachaData($fetchOther, $gameFetchOtherData->usrGachas, true);
        $fetchOther = $this->responseDataFactory->addUsrShopPassData($fetchOther, $gameFetchOtherData->usrShopPasses);
        $fetchOther = $this->responseDataFactory->addUsrItemTradeData($fetchOther, $gameFetchOtherData->usrItemTrades, true);
        $fetchOther = $this->responseDataFactory->addUsrEnemyDiscoveryData(
            $fetchOther,
            $gameFetchOtherData->usrEnemyDiscoveries,
        );
        $fetchOther = $this->responseDataFactory->addAdventBattleRaidTotalScoreData(
            $fetchOther,
            $gameFetchOtherData->adventBattleRaidTotalScoreData,
        );
        $fetchOther = $this->responseDataFactory->addUsrTutorialFreePartData(
            $fetchOther,
            $gameFetchOtherData->freePartUsrTutorialDataList,
        );
        $fetchOther = $this->responseDataFactory->addBnidLinkedAtData($fetchOther, $gameFetchOtherData->bnidLinkedAt);
        $fetchOther = $this->responseDataFactory->addGameStartAtData($fetchOther, $gameFetchOtherData->usrUser);
        $fetchOther = $this->responseDataFactory->addSysPvpSeasonData($fetchOther, $gameFetchOtherData->sysPvpSeasonEntity);
        $fetchOther = $this->responseDataFactory->addUsrPvpStatusData($fetchOther, $gameFetchOtherData->usrPvpStatusData);
        $fetchOther = $this->responseDataFactory->addMngContentCloseData($fetchOther, $gameFetchOtherData->mngContentCloses);
        $fetchOther = $this->responseDataFactory->addUsrExchangeLineupData($fetchOther, $gameFetchOtherData->usrExchangeLineups);

        $update = [];

        $result['fetchOther'] = $fetchOther;
        $result['update'] = $update;

        $result = $this->addFetchResponse($result, $resultData->gameFetchData);

        return response()->json($result);
    }

    public function createServerTimeResponse(GameServerTimeResultData $resultData): JsonResponse
    {
        $result = [];

        $result['serverTime'] = StringUtil::convertToISO8601($resultData->serverTime->toDateTimeString());

        return response()->json($result);
    }

    public function createFetchResponse(GameFetchResultData $resultData): JsonResponse
    {
        $result = $this->addFetchResponse([], $resultData->gameFetchData);

        return response()->json($result);
    }

    public function createBadgeResponse(GameBadgeResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addGameBadgeData($result, $resultData->gameBadgeData);
        $result = $this->responseDataFactory->addMngContentCloseData($result, $resultData->mngContentCloses);

        return response()->json($result);
    }
}
