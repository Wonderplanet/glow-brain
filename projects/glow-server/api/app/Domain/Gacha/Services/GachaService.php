<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Factories\LotteryFactory;
use App\Domain\Common\Utils\MathUtil;
use App\Domain\Gacha\Constants\GachaConstants;
use App\Domain\Gacha\Entities\GachaBoxInterface;
use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Entities\GachaLotteryBox;
use App\Domain\Gacha\Entities\GachaPrize;
use App\Domain\Gacha\Entities\GachaPrizeProbability;
use App\Domain\Gacha\Entities\GachaProbabilityGroup;
use App\Domain\Gacha\Entities\GachaRarityProbability;
use App\Domain\Gacha\Entities\GachaResultData;
use App\Domain\Gacha\Entities\GachaUpperProbability;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Manager\CostConsumptionManager;
use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\Gacha\Repositories\UsrGachaRepository;
use App\Domain\Gacha\Repositories\UsrGachaUpperRepository;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstItemEntity;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity;
use App\Domain\Resource\Mst\Entities\OprGachaUpperEntity;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaI18nRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaPrizeRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaUpperRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaUseResourceRepository;
use App\Http\Responses\Data\GachaProbabilityData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class GachaService
{
    public function __construct(
        // Repositories
        private readonly OprGachaRepository $oprGachaRepository,
        private readonly UsrGachaRepository $usrGachaRepository,
        private readonly OprGachaUpperRepository $oprGachaUpperRepository,
        private readonly UsrGachaUpperRepository $usrGachaUpperRepository,
        private readonly OprGachaUseResourceRepository $oprGachaUseResourceRepository,
        private readonly OprGachaPrizeRepository $oprGachaPrizeRepository,
        private readonly OprGachaI18nRepository $oprGachaI18nRepository,
        private readonly MstItemRepository $mstItemRepository,
        private readonly MstUnitRepository $mstUnitRepository,
        // Services
        private readonly GachaCacheService $gachaCacheService,
        // Common
        private readonly Clock $clock,
        private readonly LotteryFactory $lotteryFactory,
        //        private readonly CacheService $cacheService,
        // Other
        private readonly CostConsumptionManager $costConsumptionManager,
    ) {
    }

    /**
     * ガシャ取得
     *
     * @param string $oprGachaId
     *
     * @return OprGachaEntity
     */
    public function getOprGacha(string $oprGachaId)
    {
        return $this->oprGachaRepository->getActiveById($oprGachaId);
    }

    /**
     * ユーザーガシャ取得
     *
     * @param string $usrUserId
     * @param string $oprGachaId
     *
     * @return UsrGachaInterface
     */
    public function getUsrGacha(string $usrUserId, string $oprGachaId): UsrGachaInterface
    {
        $usrGacha = $this->usrGachaRepository->getByOprGachaId($usrUserId, $oprGachaId);
        if (is_null($usrGacha)) {
            // 新規作成
            return $this->usrGachaRepository->create($usrUserId, $oprGachaId);
        }
        return $this->resetUsrGachaDailyCount($usrGacha);
    }

    /**
     * ユーザーガシャ天井取得
     *
     * @param string $usrUserId
     * @param string $upperGroup
     * @param Collection $upperTypes
     * @return Collection
     */
    public function getUsrGachaUppers(string $usrUserId, string $upperGroup, Collection $upperTypes): Collection
    {
        $usrGachaUppers = $this
            ->usrGachaUpperRepository
            ->getByUpperGroupAndTypes($usrUserId, $upperGroup, $upperTypes)
            ->keyBy(function ($upper): string {
                return $upper->getUpperType();
            });
        foreach ($upperTypes as $upperType) {
            if (!$usrGachaUppers->has($upperType)) {
                // ない場合はデータを生成する
                $usrGachaUppers->put(
                    $upperType,
                    $this->usrGachaUpperRepository->create($usrUserId, $upperGroup, $upperType)
                );
            }
        }
        return $usrGachaUppers->values();
    }

    /**
     * ガチャ抽選BOX取得
     *
     * @param OprGachaEntity $oprGacha
     *
     * @return GachaLotteryBox
     * @throws GameException
     */
    public function getGachaLotteryBox(OprGachaEntity $oprGacha): GachaLotteryBox
    {
        $regularLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError(
            $oprGacha->getPrizeGroupId()
        );

        $fixedLotteryBox = null;
        if ($oprGacha->hasMultiFixedPrize()) {
            // N連の確定枠がある場合
            $fixedLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError($oprGacha->getFixedPrizeGroupId());
            if ($fixedLotteryBox->isEmpty()) {
                throw new GameException(
                    ErrorCode::GACHA_BOX_IS_EMPTY,
                    "gacha fixed box is empty (gacha id {$oprGacha->getFixedPrizeGroupId()})",
                );
            }
        }

        // 共通処理：マスターデータ取得とGachaPrize変換
        return $this->convertToGachaLotteryBox($regularLotteryBox, $fixedLotteryBox);
    }

    /**
     * OprGachaPrizeをマスターデータと紐付けてGachaLotteryBoxに変換
     *
     * @param Collection $regularLotteryBox
     * @param Collection|null $fixedLotteryBox
     * @return GachaLotteryBox
     * @throws GameException
     */
    public function convertToGachaLotteryBox(
        Collection $regularLotteryBox,
        ?Collection $fixedLotteryBox
    ): GachaLotteryBox {
        // 排出対象のユニットとアイテムのマスターデータを取得
        $allPrizes = $regularLotteryBox;
        if (!is_null($fixedLotteryBox)) {
            $allPrizes = $allPrizes->merge($fixedLotteryBox);
        }

        $mstUnitIds = collect();
        $mstItemIds = collect();
        /** @var OprGachaPrizeEntity $prize */
        foreach ($allPrizes as $prize) {
            switch ($prize->getResourceType()) {
                case RewardType::UNIT:
                    $mstUnitIds->push($prize->getResourceId());
                    break;
                case RewardType::ITEM:
                    $mstItemIds->push($prize->getResourceId());
                    break;
                case RewardType::COIN:
                    // コインはマスターデータ検証がないので何もしない
                    break;
                default:
                    throw new GameException(
                        ErrorCode::GACHA_NOT_EXPECTED_RESOURCE_TYPE,
                        sprintf('prize not expected resource type %s', $prize->getResourceType()->value),
                    );
            }
        }

        // マスターデータの存在確認
        $mstUnits = collect();
        if ($mstUnitIds->isNotEmpty()) {
            $mstUnitIds = $mstUnitIds->unique();
            $mstUnits = $this->mstUnitRepository->getByIds($mstUnitIds);
            if ($mstUnitIds->count() !== $mstUnits->count()) {
                throw new GameException(
                    ErrorCode::MST_NOT_FOUND,
                    sprintf('gacha box mst_units not found (%s)', $mstUnitIds->implode(',')),
                );
            }
        }

        $mstItems = collect();
        if ($mstItemIds->isNotEmpty()) {
            $mstItemIds = $mstItemIds->unique();
            $mstItems = $this->mstItemRepository->getActiveItemsById($mstItemIds, $this->clock->now());
            if ($mstItemIds->count() !== $mstItems->count()) {
                throw new GameException(
                    ErrorCode::MST_NOT_FOUND,
                    sprintf('gacha box mst_items not found (%s)', $mstItemIds->implode(',')),
                );
            }
            $mstItems = $mstItems->keyBy(function (MstItemEntity $entity) {
                return $entity->getId();
            });
        }

        // レアリティを付与したGachaPrizeに変換
        $regularBox = $regularLotteryBox->map(function (OprGachaPrizeEntity $prize) use ($mstUnits, $mstItems) {
            return new GachaPrize(
                $prize->getId(),
                $prize->getGroupId(),
                $prize->getResourceType(),
                $prize->getResourceId(),
                $prize->getResourceAmount(),
                $prize->getWeight(),
                $prize->getPickup(),
                $this->getRarityByPrize($prize, $mstUnits, $mstItems),
            );
        });

        $fixedBox = $fixedLotteryBox?->map(function (OprGachaPrizeEntity $prize) use ($mstUnits, $mstItems) {
            return new GachaPrize(
                $prize->getId(),
                $prize->getGroupId(),
                $prize->getResourceType(),
                $prize->getResourceId(),
                $prize->getResourceAmount(),
                $prize->getWeight(),
                $prize->getPickup(),
                $this->getRarityByPrize($prize, $mstUnits, $mstItems),
            );
        });

        return new GachaLotteryBox($regularBox, $fixedBox);
    }

    /**
     * ガチャの排出物からレアリティを取得する
     * @param OprGachaPrizeEntity $prize
     * @param Collection          $mstUnits
     * @param Collection          $mstItems
     * @return string
     * @throws GameException
     */
    private function getRarityByPrize(OprGachaPrizeEntity $prize, Collection $mstUnits, Collection $mstItems): string
    {
        return match ($prize->getResourceType()) {
            RewardType::UNIT => $mstUnits[$prize->getResourceId()]->getRarity(),
            RewardType::ITEM => $mstItems[$prize->getResourceId()]->getRarity(),
                // コインは最低レアリティのRとする
            RewardType::COIN => RarityType::R->value,
            default => throw new GameException(
                ErrorCode::GACHA_NOT_EXPECTED_RESOURCE_TYPE,
                "gacha prize not expected resource type {$prize->getResourceType()->value}",
            ),
        };
    }

    /**
     * 既に引いている数にズレがないかチェック
     *
     * @param int $drewCount
     * @param int $usrGachaCount
     *
     * @return void
     * @throws \Throwable
     */
    public function validateDrewCount(int $drewCount, int $usrGachaCount): void
    {
        if ($drewCount !== $usrGachaCount) {
            // 既に引いている数にズレてる場合
            throw new GameException(ErrorCode::GACHA_DREW_COUNT_DIFFERENT, 'gacha drew count different');
        }
    }

    /**
     * N連数のチェック
     *
     * @param int $playNum
     * @param int $oprGachaMultiDrawCount
     *
     * @return void
     * @throws \Throwable
     */
    public function validatePlayNum(int $playNum, int $oprGachaMultiDrawCount): void
    {
        if ($playNum <= 0 || $playNum > $oprGachaMultiDrawCount) {
            // 想定外のN連数
            throw new GameException(ErrorCode::GACHA_NOT_EXPECTED_PLAY_NUM, 'gacha play_num not expected');
        }
    }

    /**
     * ガシャタイプが適切かチェック
     *
     * @param OprGachaEntity $oprGacha
     * @param CostType $costType
     *
     * @return void
     * @throws \Throwable
     */
    public function validateCostType(OprGachaEntity $oprGacha, CostType $costType): void
    {
        $validCostTypes = array_key_exists($oprGacha->getGachaType()->value, GachaConstants::PERMISSION_GACHA_COST)
            ? GachaConstants::PERMISSION_GACHA_COST[$oprGacha->getGachaType()->value]
            : [];
        if (!in_array($costType->value, $validCostTypes)) {
            // 許可されてない引き方の場合
            throw new GameException(ErrorCode::GACHA_TYPE_UNEXPECTED, 'gacha cost are not allowed');
        }

        if ($costType === CostType::AD && !$oprGacha->getEnableAdPlay()) {
            // 広告で引く場合、かつ広告で引けない設定の場合
            throw new GameException(ErrorCode::GACHA_TYPE_UNEXPECTED, 'gacha cannot ad draw');
        }
    }

    /**
     * 広告チェック
     *
     * @param OprGachaEntity $oprGacha
     * @param UsrGachaInterface $usrGacha
     *
     * @return void
     * @throws \Throwable
     */
    public function validateAd(OprGachaEntity $oprGacha, UsrGachaInterface $usrGacha): void
    {
        // 広告視聴回数検証
        if (
            !is_null($oprGacha->getTotalAdLimitCount()) &&
            $usrGacha->getAdCount() > $oprGacha->getTotalAdLimitCount()
        ) {
            throw new GameException(ErrorCode::GACHA_CANNOT_AD_LIMIT_DRAW, 'gacha cannot ad draw total count');
        }

        // 広告視聴デイリー回数検証
        if (
            !is_null($oprGacha->getDailyAdLimitCount()) &&
            $usrGacha->getAdDailyCount() > $oprGacha->getDailyAdLimitCount()
        ) {
            throw new GameException(ErrorCode::GACHA_CANNOT_AD_LIMIT_DRAW, 'gacha cannot ad draw daily count');
        }

        // 広告視聴インターバル検証
        $adPlayedAt = $usrGacha->getAdPlayedAt();
        if (
            !is_null($adPlayedAt) &&
            CarbonImmutable::parse($adPlayedAt)->addMinutes($oprGacha->getAdPlayIntervalTime())->isFuture()
        ) {
            throw new GameException(ErrorCode::GACHA_CANNOT_AD_INTERVAL_DRAW, 'gacha cannot ad draw time');
        }

        // TODO: 広告レシート検証
    }

    /**
     * ガシャ回数チェック
     *
     * @param OprGachaEntity $oprGacha
     * @param UsrGachaInterface $usrGacha
     *
     * @return void
     * @throws \Throwable
     */
    public function validatePlayCount(OprGachaEntity $oprGacha, UsrGachaInterface $usrGacha): void
    {
        // ガシャ回数検証
        if (
            !is_null($oprGacha->getTotalPlayLimitCount()) &&
            $usrGacha->getCount() > $oprGacha->getTotalPlayLimitCount()
        ) {
            throw new GameException(ErrorCode::GACHA_PLAY_LIMIT, 'gacha cannot draw total count');
        }

        // ガシャデイリー回数検証
        if (
            !is_null($oprGacha->getDailyPlayLimitCount()) &&
            $usrGacha->getDailyCount() > $oprGacha->getDailyPlayLimitCount()
        ) {
            throw new GameException(ErrorCode::GACHA_PLAY_LIMIT, 'gacha cannot ad draw daily count');
        }
    }

    /**
     * コストの妥当性チェック（通常ガチャ用）
     *
     * @param OprGachaEntity $oprGacha
     * @param int $playNum
     * @param ?string $costId
     * @param int $costNum
     * @param CostType $costType
     * @return void
     * @throws \Throwable
     */
    public function validateCost(
        OprGachaEntity $oprGacha,
        int $playNum,
        ?string $costId,
        int $costNum,
        CostType $costType
    ): void {
        $oprGachaUseResource = null;
        $expectedCostNum = null;

        // まずopr_gacha_use_resourcesから取得を試みる
        if (
            $costType->value === CostType::ITEM->value &&
            $oprGacha->getGachaType()->value !== GachaType::PAID_ONLY->value
        ) {
            /**
             * 初めはリクエストされた回数でマスタデータ取得を試みる。
             * なければ、1回引く分のマスタデータを取得して、回数分乗算して、コスト消費する。
             * どちらのマスタもなければエラー。
             */
            $oprGachaUseResource = $this->oprGachaUseResourceRepository->getByIdAndCostTypeAndDrawCount(
                $oprGacha->getId(),
                $costType->value,
                $playNum,
                false,
            );
            if ($oprGachaUseResource === null) {
                $oprGachaUseResource = $this->oprGachaUseResourceRepository->getByIdAndCostTypeAndDrawCount(
                    $oprGacha->getId(),
                    $costType->value,
                    1,
                    true,
                );

                if (!is_null($oprGachaUseResource)) {
                    $expectedCostNum = $oprGachaUseResource->getCostNum() * $playNum;
                }
            } else {
                $expectedCostNum = $oprGachaUseResource->getCostNum();
            }
        } else {
            $oprGachaUseResource = $this->oprGachaUseResourceRepository->getByIdAndCostTypeAndDrawCount(
                $oprGacha->getId(),
                $costType->value,
                $playNum,
                false,
            );
            if (!is_null($oprGachaUseResource)) {
                $expectedCostNum = $oprGachaUseResource->getCostNum();
            }
        }

        // ガチャ使用リソースが存在しない場合はエラー
        if ($oprGachaUseResource === null) {
            throw new GameException(
                ErrorCode::GACHA_UNJUST_COSTS,
                sprintf(
                    'gacha unjust costs (cost_id:%s, cost_num:%s, cost_type:%s)',
                    $costId ?? '',
                    $costNum,
                    $costType->value
                )
            );
        }

        // 基本的なコスト検証を実行
        $this->validateBasicCost(
            $costType,
            $costId,
            $costNum,
            $oprGachaUseResource->getCostId(),
            $expectedCostNum
        );
    }

    /**
     * コストの基本的な妥当性検証（共通処理）
     * cost_idとcost_numの検証を行う
     *
     * @param CostType    $costType      リクエストのコストタイプ
     * @param string|null $requestCostId リクエストのコストID
     * @param int         $requestCostNum リクエストのコスト数
     * @param string|null $expectedCostId 期待されるコストID
     * @param int         $expectedCostNum 期待されるコスト数
     * @return void
     * @throws GameException
     */
    public function validateBasicCost(
        CostType $costType,
        ?string $requestCostId,
        int $requestCostNum,
        ?string $expectedCostId,
        int $expectedCostNum
    ): void {
        // コストIDの妥当性をチェック（Itemの場合）
        if ($costType === CostType::ITEM) {
            $requestCostIdIsEmpty = is_null($requestCostId) || $requestCostId === '';
            $expectedCostIdIsEmpty = is_null($expectedCostId) || $expectedCostId === '';

            $costIdIsValid = ($requestCostIdIsEmpty && $expectedCostIdIsEmpty) ||
                            (!$requestCostIdIsEmpty && $expectedCostId === $requestCostId);

            if (!$costIdIsValid) {
                throw new GameException(
                    ErrorCode::GACHA_UNJUST_COSTS,
                    sprintf(
                        'gacha unjust costs (cost_id:%s, expected:%s, cost_num:%s, cost_type:%s)',
                        $requestCostId ?? '',
                        $expectedCostId ?? '',
                        $requestCostNum,
                        $costType->value
                    )
                );
            }
        }

        // コスト数の妥当性をチェック
        if ($expectedCostNum !== $requestCostNum) {
            throw new GameException(
                ErrorCode::GACHA_UNJUST_COSTS,
                sprintf(
                    'gacha unjust costs (cost_id:%s, cost_num:%s, expected:%d, cost_type:%s)',
                    $requestCostId ?? '',
                    $requestCostNum,
                    $expectedCostNum,
                    $costType->value
                )
            );
        }
    }

    /**
     * 消費コスト設定（通常ガチャ用）
     *
     * @param OprGachaEntity $oprGacha
     * @param UsrGachaInterface $usrGacha
     * @param string $usrUserId
     * @param int $playNum
     * @param ?string $costId,
     * @param int $costNum,
     * @param int $platform
     * @param string $billingPlatform
     * @param bool $checkedAd
     * @param CostType $costType
     *
     * @return void
     * @throws \Throwable
     */
    public function setConsumeResource(
        OprGachaEntity $oprGacha,
        UsrGachaInterface $usrGacha,
        string $usrUserId,
        int $playNum,
        ?string $costId,
        int $costNum,
        int $platform,
        string $billingPlatform,
        bool $checkedAd,
        CostType $costType
    ): void {
        // コスト消費を設定
        $oprGachaI18n = $this->oprGachaI18nRepository->getByOprGachaIdAndLanguageWithError(
            $oprGacha->getId(),
            Language::Ja
        );
        $gachaTrigger = new GachaTrigger($oprGacha->getId(), $oprGachaI18n->getName());

        $this->costConsumptionManager->setConsumeResource(
            $usrUserId,
            $costId,
            $costNum,
            $platform,
            $billingPlatform,
            $checkedAd,
            $costType,
            $gachaTrigger
        );
    }

    /**
     * 消費コストの消費実行
     *
     * @return void
     * @throws \Throwable
     */
    public function execConsumeResource(ILogGachaAction $logGachaAction): void
    {
        $this->costConsumptionManager->execConsumeResource($logGachaAction);
    }

    /**
     * ガシャマスタデータから配布用報酬インスタンスを生成
     *
     * @param Collection $gachaBoxes
     * @param string     $oprGachaId
     * @return Collection<GachaReward>
     */
    public function makeGachaRewardByGachaBoxes(Collection $gachaBoxes, string $oprGachaId): Collection
    {
        return $gachaBoxes->map(function (GachaBoxInterface $gachaBox, int $index) use ($oprGachaId) {
            return new GachaReward(
                $gachaBox->getResourceType()->value,
                $gachaBox->getResourceId(),
                $gachaBox->getResourceAmount(),
                $oprGachaId,
                $index,
            );
        });
    }

    /**
     * ガチャの抽選処理を実行（低レベル抽選ロジック）
     * 実際の抽選アルゴリズムを実行し、当選アイテムを決定する
     *
     * @param OprGachaEntity                     $oprGacha
     * @param GachaLotteryBox                $gachaLotteryBoxData
     * @param int                                $drawCount
     * @param Collection<OprGachaUpperEntity>    $oprGachaUppers
     * @param Collection<UsrGachaUpperInterface> $usrGachaUppers
     * @param bool                               $isDrawAd
     * @return GachaResultData
     * @throws GameException
     */
    public function executeLottery(
        OprGachaEntity $oprGacha,
        GachaLotteryBox $gachaLotteryBoxData,
        int $drawCount,
        Collection $oprGachaUppers,
        Collection $usrGachaUppers,
        bool $isDrawAd,
        ?int $fixedPrizeCount = null
    ): GachaResultData {
        // 確定枠の数を決定（引数で指定されている場合はそれを使用、なければガチャ設定から取得）
        $fixedCount = $fixedPrizeCount ?? $oprGacha->getMultiFixedPrizeCount();
        $regularDrawCount = $drawCount - $fixedCount;
        $regularLotteryBox = $gachaLotteryBoxData->getRegularLotteryBox();

        $oprGachaUpperMaxRarity = $oprGachaUppers->first(fn($upper) => $upper->isMaxRarity());
        $usrGachaUpperMaxRarity = $usrGachaUppers->first(fn($upper) => $upper->isMaxRarity());
        $oprGachaUpperPickup = $oprGachaUppers->first(fn($upper) => $upper->isPickup());
        $usrGachaUpperPickup = $usrGachaUppers->first(fn($upper) => $upper->isPickup());

        /**
         * 抽選の優先順位
         * 1. ピックアップ天井
         * 2. 最高レアリティ天井
         * 3. 確定枠
         */
        $drawResult = collect();
        $prizeTypes = [];
        foreach (range(1, $drawCount) as $count) {
            $this->increaseUpperCount($usrGachaUppers, $oprGacha->getEnableAddAdPlayUpper(), $isDrawAd);

            if ($this->isReachUpper($usrGachaUpperPickup, $oprGachaUpperPickup)) {
                // PickUp確定
                $prizeType = GachaPrizeType::PICKUP->value;
                $box = $this->generatePickupBox($gachaLotteryBoxData->getRegularLotteryBox());
            } elseif ($this->isReachUpper($usrGachaUpperMaxRarity, $oprGachaUpperMaxRarity)) {
                // 最高レアリティから抽選
                $prizeType = GachaPrizeType::MAX_RARITY->value;
                $box = $this->generateMaxRarityBox($gachaLotteryBoxData->getRegularLotteryBox());
            } else {
                if ($drawCount < GachaConstants::MINIMUM_DRAW_COUNT_FOR_FIXED || $count <= $regularDrawCount) {
                    // 10連未満か、通常枠の範囲は通常抽選
                    $prizeType = GachaPrizeType::REGULAR->value;
                    $box = $regularLotteryBox;
                } else {
                    // 最低保証抽選
                    $prizeType = GachaPrizeType::FIXED->value;
                    $box = $gachaLotteryBoxData->getFixedLotteryBox();
                }
            }
            $prizeTypes[] = $prizeType;
            $prize = $this->lottery($box);
            $drawResult->push($prize);

            $this->resetUsrUpperCount($prize, $usrGachaUppers, $oprGacha->getEnableAddAdPlayUpper(), $isDrawAd);
        }
        return new GachaResultData($oprGacha->getId(), $drawResult, $prizeTypes);
    }

    /**
     * 抽選BOXから最大レアリティ(UR)のみを取得
     *
     * @param Collection<GachaBoxInterface> $gachaPrizes
     * @return Collection<GachaBoxInterface>
     */
    public function generateMaxRarityBox(Collection $gachaPrizes): Collection
    {
        $box = collect();
        /** @var GachaBoxInterface $prize */
        foreach ($gachaPrizes as $prize) {
            if ($prize->getRarity() === $this->getMaxRarity()) {
                $box->push($prize);
            }
        }

        return $box;
    }

    /**
     * 抽選BOXから最高レアリティのピックアップのみを取得
     *
     * @param Collection<GachaBoxInterface> $gachaPrizes
     * @return Collection<GachaBoxInterface>
     * @throws GameException
     */
    public function generatePickupBox(Collection $gachaPrizes): Collection
    {
        $prizes = $gachaPrizes->filter(
            fn(GachaBoxInterface $oprGachaPrizeEntity) => $oprGachaPrizeEntity->getPickup()
        );
        return $this->generateMaxRarityBox($prizes);
    }

    /**
     * 天井に到達したか
     *
     * @param UsrGachaUpperInterface|null $usrGachaUpper
     * @param OprGachaUpperEntity|null $oprGachaUpper
     *
     * @return bool
     */
    public function isReachUpper(
        ?UsrGachaUpperInterface $usrGachaUpper,
        ?OprGachaUpperEntity $oprGachaUpper
    ): bool {
        if (is_null($oprGachaUpper) || is_null($usrGachaUpper)) {
            return false;
        }

        return $usrGachaUpper->getCount() === $oprGachaUpper->getCount();
    }

    /**
     * 最高レア(UR)のユニットを獲得したか
     *
     * @param GachaBoxInterface $prize
     *
     * @return bool
     */
    public function isGetMaxRarity(GachaBoxInterface $prize): bool
    {
        return $prize->getRarity() === $this->getMaxRarity();
    }

    public function getMaxRarity(): string
    {
        // ガチャ天井時に排出される最高レアリティ
        return GachaConstants::MAX_RARITY->value;
    }

    /**
     * ユーザ天井情報のリセット
     *
     * @param GachaBoxInterface $prize
     * @param Collection<UsrGachaUpperInterface>|null   $usrGachaUppers
     * @param bool              $enableAddAdPlayUpper
     * @param bool              $isDrawAd
     *
     * @return void
     * @throws GameException
     */
    protected function resetUsrUpperCount(
        GachaBoxInterface $prize,
        ?Collection $usrGachaUppers,
        bool $enableAddAdPlayUpper,
        bool $isDrawAd
    ): void {
        if ($isDrawAd && !$enableAddAdPlayUpper) {
            return;
        }

        if (is_null($usrGachaUppers)) {
            return;
        }

        $isMaxRarity = $this->isGetMaxRarity($prize);
        if ($prize->getPickup() && $isMaxRarity) {
            // ピックアップが排出された
            foreach ($usrGachaUppers as $usrGachaUpper) {
                /** @var UsrGachaUpperInterface $usrGachaUpper */
                $usrGachaUpper->resetCount();
            }
        } elseif ($isMaxRarity) {
            /** @var UsrGachaUpperInterface|null $upper */
            $upper = $usrGachaUppers->filter(function (UsrGachaUpperInterface $usrGachaUpper) {
                return $usrGachaUpper->getUpperType() === UpperType::MAX_RARITY->value;
            })->first();
            $upper?->resetCount();
        }
    }

    /**
     * ユーザ天井情報のカウントアップ
     *
     * @param Collection $usrGachaUppers
     * @param bool       $enableAddAdPlayUpper
     * @param bool       $isDrawAd
     *
     * @return void
     */
    protected function increaseUpperCount(
        Collection $usrGachaUppers,
        bool $enableAddAdPlayUpper,
        bool $isDrawAd
    ): void {
        if ($isDrawAd && !$enableAddAdPlayUpper) {
            return;
        }

        foreach ($usrGachaUppers as $usrGachaUpper) {
            /** @var UsrGachaUpperInterface $usrGachaUpper */
            $usrGachaUpper->addCount();
        }
    }

    /**
     * @param Collection<GachaBoxInterface> $oprGachaPrizes
     *
     * @return GachaBoxInterface
     * @throws GameException
     */
    public function lottery(Collection $oprGachaPrizes): GachaBoxInterface
    {
        $weightMap = $oprGachaPrizes->mapWithKeys(function (GachaBoxInterface $entity) {
            return [$entity->getId() => $entity->getWeight()];
        });

        $contentMap = $oprGachaPrizes->mapWithKeys(function (GachaBoxInterface $entity) {
            return [$entity->getId() => $entity];
        });

        $lottery = $this->lotteryFactory->createFromMaps($weightMap, $contentMap);
        $prize = $lottery->draw();
        if (is_null($prize)) {
            throw new GameException(ErrorCode::NO_LOTTERY_RESULT);
        }
        return $prize;
    }

    /**
     * ユーザデータの保存
     *
     * @param UsrGachaInterface $usrGacha
     * @param Collection $usrGachaUppers
     *
     * @return void
     */
    public function saveUsr(UsrGachaInterface $usrGacha, Collection $usrGachaUppers): void
    {
        $this->usrGachaRepository->syncModel($usrGacha);
        $usrGachaUppers->each(function (UsrGachaUpperInterface $usrGachaUpper) {
            $this->usrGachaUpperRepository->syncModel($usrGachaUpper);
        });
    }

    /**
     * 期間内のユーザーガシャとユーザーガシャ天井を取得
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     *
     * @return array<mixed, mixed>
     */
    public function getActiveGachas(string $usrUserId, CarbonImmutable $now): array
    {
        // ガシャ関係
        $oprGachas = $this->oprGachaRepository->getActive($now);
        $gachaIds = collect();
        $gachaUpperGroups = collect();
        foreach ($oprGachas as $oprGacha) {
            /** @var OprGachaEntity $oprGacha */
            $gachaIds->push($oprGacha->getId());
            if ($oprGacha->hasUpper()) {
                $gachaUpperGroups->push($oprGacha->getUpperGroup());
            }
        }
        $usrGachas = collect();
        if ($gachaIds->isNotEmpty()) {
            $usrGachas = $this->usrGachaRepository->getByOprGachaIds(
                $usrUserId,
                $gachaIds
            )->map(function ($usrGacha) {
                return $this->resetUsrGachaDailyCount($usrGacha);
            });
        }
        $usrGachaUppers = collect();
        if ($gachaUpperGroups->isNotEmpty()) {
            $usrGachaUppers = $this->usrGachaUpperRepository->getByUpperGroups(
                $usrUserId,
                $gachaUpperGroups
            );
        }
        return [$usrGachas, $usrGachaUppers];
    }

    /**
     * 提供割合
     */

    /**
     * 提供割合データを生成
     * @param string $oprGachaId
     * @return GachaProbabilityData
     * @throws GameException
     */
    public function generateGachaProbability(string $oprGachaId): GachaProbabilityData
    {
        $oprGacha = $this->oprGachaRepository->getByIdWithError($oprGachaId);

        // FIXME 開発期間中はデータ更新が頻繁に行われるので一旦キャッシュを無効化する
//        $gachaProbabilityData = $this->cacheService->getGachaProbability($oprGachaId);
//        if (!is_null($gachaProbabilityData)) {
//            return $gachaProbabilityData;
//        }

        $gachaLotteryBoxData = $this->getGachaLotteryBox($oprGacha);

        $regularLotteryBox = $gachaLotteryBoxData->getRegularLotteryBox();
        $rarityProbabilities = $this->generateRarityProbability($regularLotteryBox);

        // 通常の提供割合を計算
        $probabilityGroups = $this->generatePrizeProbabilityByPrize($regularLotteryBox);

        // 確定枠の提供割合を計算
        $fixedLotteryBox = $gachaLotteryBoxData->getFixedLotteryBox() ?? collect();
        $fixedProbabilityGroups = $this->generatePrizeProbabilityByPrize($fixedLotteryBox);
        $fixedRarityProbabilities = $this->generateRarityProbability($fixedLotteryBox);

        // 天井の提供割合を計算
        $upperProbabilities = collect();
        $oprGachaUppers = $this->oprGachaUpperRepository->getByUpperGroup($oprGacha->getUpperGroup());
        foreach ($oprGachaUppers as $oprGachaUpper) {
            /** @var OprGachaUpperEntity $oprGachaUpper */
            if ($oprGachaUpper->isMaxRarity()) {
                $prizeBox = $this->generateMaxRarityBox($regularLotteryBox);
            } elseif ($oprGachaUpper->isPickup()) {
                $prizeBox = $this->generatePickupBox($regularLotteryBox);
            } else {
                throw new GameException(
                    ErrorCode::MST_NOT_FOUND,
                    "gacha upper not expected.(upper_type:{$oprGachaUpper->getUpperType()->value})"
                );
            }
            $upperProbabilityGroups = $this->generatePrizeProbabilityByPrize($prizeBox);
            $upperRarityProbabilities = $this->generateRarityProbability($prizeBox);
            $upperProbabilities->add(
                new GachaUpperProbability(
                    $oprGachaUpper->getUpperType()->value,
                    $upperProbabilityGroups,
                    $upperRarityProbabilities
                )
            );
        }

        $gachaProbabilityData = new GachaProbabilityData(
            $rarityProbabilities,
            $probabilityGroups,
            $oprGacha->getMultiFixedPrizeCount(),
            $fixedProbabilityGroups,
            $fixedRarityProbabilities,
            $upperProbabilities
        );
        $gachaProbabilityData->formatToResponse();

        // FIXME 開発期間中はデータ更新が頻繁に行われるので一旦キャッシュを無効化する
//        $this->cacheService->setGachaProbability($oprGachaId, $gachaProbabilityData);
        return $gachaProbabilityData;
    }

    /**
     * レアリティの提供割合を計算
     * @param Collection $lotteryBox
     * @return Collection
     */
    public function generateRarityProbability(Collection $lotteryBox): Collection
    {
        // レアリティごとの確率を計算
        $totalWeight = $lotteryBox->sum(fn($prize) => $prize->getWeight());
        $rarities = $lotteryBox->map(fn($prize) => $prize->getRarity())->unique();
        $rarityProbabilities = collect();
        foreach ($rarities as $rarity) {
            $rarityWeight = $lotteryBox
                ->filter(fn($prize) => $prize->getRarity() === $rarity)
                ->sum(fn($prize) => $prize->getWeight());
            $probability = $this->calcProbabilityPercent($rarityWeight, $totalWeight);
            $rarityProbabilities->add(new GachaRarityProbability($rarity, $probability));
        }
        return $rarityProbabilities;
    }

    /**
     * レアリティごとの提供割合を計算
     *
     * @param Collection<GachaBoxInterface> $prizeBox
     * @return Collection<GachaProbabilityGroup>
     */
    public function generatePrizeProbabilityByPrize(Collection $prizeBox): Collection
    {
        $totalWeight = $prizeBox->sum(fn($prize) => $prize->getWeight());
        $rarityPrizeMap = $prizeBox->groupBy(function ($prize): string {
            return $prize->getRarity();
        });

        $probabilityGroup = collect();
        foreach ($rarityPrizeMap as $rarity => $prizes) {
            $prizeProbabilities = collect();
            foreach ($prizes as $prize) {
                $probability = $this->calcProbabilityPercent($prize->getWeight(), $totalWeight);
                $prizeProbabilities->add(new GachaPrizeProbability($prize, $probability));
            }
            $probabilityGroup->add(new GachaProbabilityGroup($rarity, $prizeProbabilities));
        }
        return $probabilityGroup;
    }

    /**
     * 重みから％(小数第４位を切り捨て)に変換
     *
     * @param int $weight
     * @param int $totalWeight
     *
     * @return float
     */
    public function calcProbabilityPercent(int $weight, int $totalWeight): float
    {
        return MathUtil::floorToPrecision(($weight / $totalWeight) * 100, 3);
    }

    /**
     * ユーザーガシャのデイリー回数リセット
     *
     * @param UsrGachaInterface $usrGacha
     *
     * @return UsrGachaInterface
     */
    private function resetUsrGachaDailyCount(UsrGachaInterface $usrGacha): UsrGachaInterface
    {
        if (!is_null($usrGacha->getPlayedAt()) && $this->clock->isFirstToday($usrGacha->getPlayedAt())) {
            $usrGacha->resetDailyCount();
        }
        if (!is_null($usrGacha->getAdPlayedAt()) && $this->clock->isFirstToday($usrGacha->getAdPlayedAt())) {
            $usrGacha->resetAdDailyCount();
        }
        return $usrGacha;
    }

    /**
     * ガシャの有効期限内か検証
     * @param UsrGachaInterface $usrGacha
     * @param CarbonImmutable   $now
     * @return void
     * @throws GameException
     */
    public function validateExpiration(UsrGachaInterface $usrGacha, CarbonImmutable $now): void
    {
        $expiresAt = $usrGacha->getExpiresAt();
        if (!is_null($expiresAt) && $usrGacha->getExpiresAt() < $now) {
            // ガシャに有効期限があり有効期限が過ぎている場合はエラー
            throw new GameException(ErrorCode::GACHA_EXPIRED, 'gacha expired');
        }
    }

    public function addGachaHistory(
        string $usrUserId,
        string $oprGachaId,
        string $costType,
        ?string $costId,
        int $costNum,
        int $playNum,
        CarbonImmutable $now,
        Collection $gachaRewards,
        ?int $stepNumber = null,
        ?int $loopCount = null,
        ?Collection $stepRewards = null
    ): void {
        $gachaHistory = new GachaHistory(
            $oprGachaId,
            $costType,
            $costId,
            $costNum,
            $playNum,
            $now,
            $gachaRewards,
            $stepNumber,
            $loopCount,
            $stepRewards
        );
        $this->gachaCacheService->prependGachaHistory($usrUserId, $gachaHistory);
    }

    /**
     * ガシャ履歴を取得する
     *
     * @param string          $usrUserId
     * @param CarbonImmutable $now
     *
     * @return Collection<GachaHistory>
     */
    public function getGachaHistories(string $usrUserId, CarbonImmutable $now): Collection
    {
        $gachaHistories = $this->gachaCacheService->getGachaHistories($usrUserId);
        if (is_null($gachaHistories)) {
            // 履歴無し
            return collect();
        }

        // 表示範囲外の履歴を除外
        $gachaHistories = $gachaHistories->filter(function (GachaHistory $history) use ($now) {
            return $history->getPlayedAt()->isAfter($now->subDays(GachaConstants::HISTORY_DAYS));
        });

        // 新しい順にソート
        return $gachaHistories->sortByDesc(function (GachaHistory $history) {
            return $history->getPlayedAt()->timestamp;
        })->values();
    }
}
