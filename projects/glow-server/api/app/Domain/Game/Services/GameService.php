<?php

declare(strict_types=1);

namespace App\Domain\Game\Services;

use App\Domain\AdventBattle\Services\AdventBattleRankingService;
use App\Domain\AdventBattle\Services\AdventBattleService;
use App\Domain\Auth\Delegators\AuthDelegator;
use App\Domain\Campaign\Services\CampaignService;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\DailyBonus\Delegators\DailyBonusDelegator;
use App\Domain\Emblem\Repositories\UsrEmblemRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkFragmentRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkRepository;
use App\Domain\Encyclopedia\Repositories\UsrReceivedUnitEncyclopediaRewardRepository;
use App\Domain\Exchange\Services\ExchangeService;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\IdleIncentive\Repositories\UsrIdleIncentiveRepository;
use App\Domain\InGame\Repositories\UsrEnemyDiscoveryRepository;
use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Item\Repositories\UsrItemTradeRepository;
use App\Domain\Message\Delegator\MessageDelegator;
use App\Domain\Message\Services\UsrMessageService;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Services\MissionBadgeService;
use App\Domain\Mission\Services\MissionEventDailyBonusFetchService;
use App\Domain\Mission\Services\MissionStatusService;
use App\Domain\Outpost\Repositories\UsrOutpostEnhancementRepository;
use App\Domain\Outpost\Repositories\UsrOutpostRepository;
use App\Domain\Party\Repositories\UsrPartyRepository;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Resource\Entities\Rewards\ComebackBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward;
use App\Domain\Resource\Mst\Repositories\MngContentCloseRepository;
use App\Domain\Resource\Mst\Repositories\OprProductRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\Shop\Repositories\UsrConditionPackRepository;
use App\Domain\Shop\Repositories\UsrShopPassRepository;
use App\Domain\Shop\Repositories\UsrStoreProductRepository;
use App\Domain\Shop\Services\AppShopService;
use App\Domain\Shop\Services\ShopService;
use App\Domain\Shop\Services\WebStoreUserService;
use App\Domain\Stage\Delegators\StageDelegator;
use App\Domain\Stage\Repositories\UsrStageEventRepository;
use App\Domain\Stage\Repositories\UsrStageRepository;
use App\Domain\Stage\Services\StageService;
use App\Domain\Tutorial\Services\TutorialStatusService;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\User\Delegators\UserDelegator;
use App\Domain\User\Repositories\UsrUserLoginRepository;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Domain\User\Services\UserBuyStaminaService;
use App\Http\Responses\Data\GameBadgeData;
use App\Http\Responses\Data\GameFetchData;
use App\Http\Responses\Data\GameFetchOtherData;
use App\Http\Responses\Data\GameUpdateData;
use App\Http\Responses\Data\MissionStatusData;
use App\Http\Responses\Data\UsrInGameStatusData;
use App\Http\Responses\Data\UsrParameterData;
use Carbon\CarbonImmutable;

class GameService
{
    public function __construct(
        private IgnService $ignService,
        // MstRepository
        private OprProductRepository $oprProductRepository,
        private MngContentCloseRepository $mngContentCloseRepository,
        // UsrRepository
        private UsrUserParameterRepository $usrUserParameterRepository,
        private UsrStageRepository $usrStageRepository,
        private UsrStageEventRepository $usrStageEventRepository,
        private UsrUserRepository $usrUserRepository,
        private UsrUserProfileRepository $usrUserProfileRepository,
        private UsrUnitRepository $usrUnitRepository,
        private UsrItemRepository $usrItemRepository,
        private UsrStoreProductRepository $usrStoreProductRepository,
        private UsrConditionPackRepository $usrConditionPackRepository,
        private UsrIdleIncentiveRepository $usrIdleIncentiveRepository,
        private UsrPartyRepository $usrPartyRepository,
        private UsrOutpostRepository $usrOutpostRepository,
        private UsrOutpostEnhancementRepository $usrOutpostEnhancementRepository,
        private UsrUserLoginRepository $usrUserLoginRepository,
        private UsrEmblemRepository $usrEmblemRepository,
        private UsrArtworkRepository $usrArtworkRepository,
        private UsrArtworkFragmentRepository $usrArtworkFragmentRepository,
        private UsrReceivedUnitEncyclopediaRewardRepository $usrReceivedUnitEncyclopediaRewardRepository,
        private ExchangeService $exchangeService,
        private UsrShopPassRepository $usrShopPassRepository,
        private UsrItemTradeRepository $usrItemTradeRepository,
        private UsrEnemyDiscoveryRepository $usrEnemyDiscoveryRepository,
        // Service
        private StageService $stageService,
        private AdventBattleService $adventBattleService,
        private MissionBadgeService $missionBadgeService,
        private MissionStatusService $missionStatusService,
        private UsrMessageService $usrMessageService,
        private MissionEventDailyBonusFetchService $missionEventDailyBonusFetchService,
        private CampaignService $campaignService,
        private GachaService $gachaService,
        private AdventBattleRankingService $adventBattleRankingService,
        private AppShopService $appShopService,
        private ShopService $shopService,
        private TutorialStatusService $tutorialStatusService,
        private UserBuyStaminaService $userBuyStaminaService,
        private WebStoreUserService $webStoreUserService,
        // Delegator
        private AuthDelegator $authDelegator,
        private UserDelegator $userDelegator,
        private StageDelegator $stageDelegator,
        private ShopDelegator $shopDelegator,
        private IdleIncentiveDelegator $idleIncentiveDelegator,
        private AppCurrencyDelegator $appCurrencyDelegator,
        private MissionDelegator $missionDelegator,
        private MessageDelegator $messageDelegator,
        private RewardDelegator $rewardDelegator,
        private DailyBonusDelegator $dailyBonusDelegator,
        private PvpService $pvpService,
    ) {
    }

    /**
     * @param string          $usrUserId
     * @param int             $platform
     * @param CarbonImmutable $now
     * @param string          $language
     * @param CarbonImmutable $gameStartAt
     * @param string|null     $countryCode 国コード（WebStore用、オプション）
     * @param string|null     $adId
     * @return GameUpdateData
     */
    public function update(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt,
        ?string $countryCode = null,
        ?string $adId = null
    ): GameUpdateData {
        // WebStore情報登録（国コード、OSプラットフォーム、広告ID）
        $this->webStoreUserService->registerWebStoreInfo($usrUserId, $countryCode, $platform, $adId);

        $usrParameter = $this->userDelegator->getUsrUserParameterWithRecoveryStamina(
            $usrUserId,
            $now,
        );
        $this->shopDelegator->releaseConditionPacks($usrUserId, $usrParameter->getLevel(), $now);

        $this->idleIncentiveDelegator->resetReceiveCount($usrUserId, $now);
        $this->stageDelegator->resetStageEvent($usrUserId, $now);

        // パスのログイン報酬を配布する
        $this->shopService->updateDailyPassReward($usrUserId, $now);

        $this->userDelegator->incrementLoginCountAndProcessActions($usrUserId, $platform, $now);

        // fetchでのバッジ情報取得のために、ミッション進捗更新をここで実行する
        $this->missionDelegator->handleAllUpdateTriggeredMissions($usrUserId, $now);

        $this->messageDelegator->addNewMessages($usrUserId, $now, $language, $gameStartAt);

        return new GameUpdateData();
    }

    public function fetch(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): GameFetchData {
        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);
        $summary = $this->appCurrencyDelegator->getCurrencySummary($usrUserId);
        $usrParameterData = new UsrParameterData(
            $usrUserParameter->getLevel(),
            $usrUserParameter->getExp(),
            $usrUserParameter->getCoin(),
            $usrUserParameter->getStamina(),
            $usrUserParameter->getStaminaUpdatedAt(),
            $summary->getFreeAmount(),
            $summary->getPaidAmountApple(),
            $summary->getPaidAmountGoogle(),
        );

        $usrStages = $this->usrStageRepository->getListByUsrUserId($usrUserId);
        $usrStageEvents = $this->stageService->resetStageEventSpeedAttack(
            $now,
            $this->usrStageEventRepository->getListByUsrUserId($usrUserId)
        );
        $usrStageEnhanceStatusDataList = $this->stageService->fetchUsrStageEnhanceStatusDataList(
            $usrUserId,
            $now,
        );

        // 降臨バトル
        $usrAdventBattles = $this->adventBattleService->fetchUsrAdventBattleList(
            $usrUserId,
            $now,
        );

        $gameBadgeData = $this->fetchBadge($usrUserId, $now, $language, $gameStartAt);

        $usrUserBuyCount = $this->userBuyStaminaService->getUsrUserBuyCountAndReset($usrUserId);

        return new GameFetchData(
            $usrParameterData,
            $usrStages,
            $usrStageEvents,
            $usrStageEnhanceStatusDataList,
            $usrAdventBattles,
            $gameBadgeData,
            $usrUserBuyCount,
            new MissionStatusData(
                $this->missionStatusService->isBeginnerMissionCompleted($usrUserId)
            ),
        );
    }

    public function fetchOther(
        string $usrUserId,
        CarbonImmutable $now,
        string $accessToken,
        string $language,
    ): GameFetchOtherData {
        $usrUser = $this->usrUserRepository->findById($usrUserId);
        $usrUserProfile = $this->usrUserProfileRepository->findByUsrUserId($usrUserId);

        $usrUnits = $this->usrUnitRepository->getListByUsrUserId($usrUserId);
        $usrItems = $this->usrItemRepository->getList($usrUserId);

        $oprProducts = $this->oprProductRepository->getActiveProducts($now);
        $usrStoreProducts = $this->usrStoreProductRepository->getList($usrUserId);
        $usrShopItems = $this->shopService->fetchResetActiveUsrShopItemsWithoutSyncModels($usrUserId, $now);
        $usrConditionPacks = $this->usrConditionPackRepository->getList($usrUserId);
        $usrIdleIncentive = $this->usrIdleIncentiveRepository->get($usrUserId);
        $usrParties = $this->usrPartyRepository->getList($usrUserId);
        $usrOutposts = $this->usrOutpostRepository->getList($usrUserId);
        $usrOutpostEnhancements = $this->usrOutpostEnhancementRepository->getList($usrUserId);
        $usrInGameStatus = $this->getActiveUsrInGameStatusData($usrUserId, $now);
        $usrUserLogin = $this->usrUserLoginRepository->get($usrUserId);
        $usrEventDailyBonusProgresses = $this->missionEventDailyBonusFetchService->fetchProgresses($usrUserId, $now);
        $usrComebackBonusProgresses = $this->dailyBonusDelegator->fetchComebackBonusProgresses($usrUserId, $now);
        $usrEmblems = $this->usrEmblemRepository->findByUsrUserId($usrUserId);
        $usrArtworks = $this->usrArtworkRepository->getList($usrUserId);
        $usrArtworkFragments = $this->usrArtworkFragmentRepository->getList($usrUserId);
        $usrReceivedUnitEncyclopediaRewards = $this
            ->usrReceivedUnitEncyclopediaRewardRepository
            ->getList($usrUserId);
        $mngInGameNoticeDataList = $this->ignService->fetchMngInGameNoticeDataList($language, $now);
        $oprCampaignDataList = $this->campaignService->getOprCampaignDataList($now);
        [$usrGachas, $usrGachaUppers] = $this->gachaService->getActiveGachas($usrUserId, $now);
        $usrShopPasses = $this->usrShopPassRepository->getActiveList($usrUserId, $now);
        $usrItemTrades = $this->usrItemTradeRepository->getList($usrUserId);
        $usrEnemyDiscoveries = $this->usrEnemyDiscoveryRepository->getList($usrUserId);
        $adventBattleRaidTotalScoreData = $this->adventBattleRankingService->getRaidTotalScoreData($now);
        $freePartUsrTutorials = $this->tutorialStatusService->getCompletedFreePartUsrTutorials($usrUserId, $now);
        $bnidLinkedAt = $this->authDelegator->findUsrDeviceByAccessToken($accessToken)?->getBnidLinkedAt();
        $usrTradePacks = $this->shopDelegator->getUsrTradePacks($usrUserId, $now);
        // 課金基盤
        $usrStoreInfo = $this->appShopService->fetchUsrStoreInfo($usrUserId, $now);

        // PVP情報を取得
        $pvpLoginData = $this->pvpService->fetchPvpLoginData(
            $usrUserId,
            $now,
        );

        // コンテンツクローズ一覧を取得（is_valid=1のみ、時刻に関係なく全て）
        $mngContentCloses = $this->mngContentCloseRepository->findActiveList();

        // データ更新無しでリセット後の交換ラインナップを取得
        $usrExchangeLineups = $this->exchangeService->fetchResetUsrExchangeLineupsWithoutSyncModels($usrUserId, $now);

        // WebStore購入商品IDを取得してクリア
        $unnotifiedProductSubIds = $this->shopDelegator->getUnnotifiedProductSubIdsAndClear(
            $usrUserId
        );

        return new GameFetchOtherData(
            $usrUser,
            $usrUserProfile,
            $usrUnits,
            $usrItems,
            $oprProducts,
            $usrStoreProducts,
            $usrShopItems,
            $usrTradePacks,
            $usrConditionPacks,
            $usrIdleIncentive,
            $usrParties,
            $usrOutposts,
            $usrOutpostEnhancements,
            $usrInGameStatus,
            $usrUserLogin,
            $this->rewardDelegator->getSentRewards(MissionDailyBonusReward::class),
            $usrEventDailyBonusProgresses,
            $this->rewardDelegator->getSentRewards(MissionEventDailyBonusReward::class),
            $usrComebackBonusProgresses,
            $this->rewardDelegator->getSentRewards(ComebackBonusReward::class),
            $usrEmblems,
            $usrArtworks,
            $usrArtworkFragments,
            $usrReceivedUnitEncyclopediaRewards,
            $mngInGameNoticeDataList,
            $usrStoreInfo,
            $oprCampaignDataList,
            $usrGachas,
            $usrGachaUppers,
            $usrShopPasses,
            $usrItemTrades,
            $usrEnemyDiscoveries,
            $adventBattleRaidTotalScoreData,
            $freePartUsrTutorials,
            $bnidLinkedAt,
            $pvpLoginData->getSysPvpSeasonEntity(),
            $pvpLoginData->getUsrPvpStatusData(),
            $mngContentCloses,
            $usrExchangeLineups,
            $unnotifiedProductSubIds,
        );
    }

    /**
     * アクティブなインゲームセッション情報を取得する
     *
     * @param string $usrUserId
     * @return UsrInGameStatusData
     */
    private function getActiveUsrInGameStatusData(string $usrUserId, CarbonImmutable $now): UsrInGameStatusData
    {
        $usrStageStatus = $this->stageService->makeUsrStageStatusData($usrUserId, $now);
        if ($usrStageStatus->getIsStartedSession()) {
            return $usrStageStatus;
        }

        $usrAdventBattleStatus = $this->adventBattleService->makeUsrAdventBattleStatusData($usrUserId);
        if ($usrAdventBattleStatus->getIsStartedSession()) {
            return $usrAdventBattleStatus;
        }

        $usrPvpStatus = $this->pvpService->makeUsrPvpInGameStatusData($usrUserId);
        if ($usrPvpStatus->getIsStartedSession()) {
            return $usrPvpStatus;
        }

        // アクティブなセッション情報がない場合は空のデータを返す
        return new UsrInGameStatusData();
    }

    public function fetchBadge(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): GameBadgeData {
        // ミッションの未受取報酬数を取得
        $missionUnreceivedRewardData = $this->missionBadgeService->fetchUnreceivedRewardData(
            $usrUserId,
            $now,
        );
        $missionUnreceivedEventRewardData = $this->missionBadgeService
            ->fetchUnreceivedEventRewardCount(
                $usrUserId,
                $now
            );
        $missionLimitedTermRewardData = $this->missionBadgeService
            ->fetchUnreceivedLimitedTermRewardCount(
                $usrUserId,
                $now,
            );

        $unopenedMessageCount = $this->usrMessageService->getUnopenedMessageCount(
            $usrUserId,
            $now,
            $language,
            $gameStartAt
        );
        $unreceivedMissionAdventBattleRewardCount = $missionLimitedTermRewardData->getAdventBattleCount();

        return new GameBadgeData(
            $missionUnreceivedRewardData->getUnreceivedMissionRewardCount(),
            $missionUnreceivedRewardData->getUnreceivedMissionBeginnerRewardCount(),
            $unopenedMessageCount,
            $missionUnreceivedEventRewardData->getCountForEventId(),
            $unreceivedMissionAdventBattleRewardCount,
        );
    }
}
