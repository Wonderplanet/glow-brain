<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Entities\GachaLotteryBox;
use App\Domain\Gacha\Entities\StepUpGachaState;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Resource\Entities\Rewards\StepUpGachaStepReward;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity;
use App\Domain\Resource\Mst\Entities\OprStepUpGachaEntity;
use App\Domain\Resource\Mst\Entities\OprStepUpGachaStepEntity;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaPrizeRepository;
use App\Domain\Resource\Mst\Repositories\OprStepUpGachaRepository;
use App\Domain\Resource\Mst\Repositories\OprStepUpGachaStepRepository;
use App\Domain\Resource\Mst\Repositories\OprStepUpGachaStepRewardRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Http\Responses\Data\StepUpGachaPrizeInfoData;
use Illuminate\Support\Collection;

/**
 * ステップアップガチャ専用サービス
 */
class StepUpGachaService
{
    public function __construct(
        private OprStepUpGachaRepository $oprStepUpGachaRepository,
        private OprStepUpGachaStepRepository $oprStepUpGachaStepRepository,
        private OprStepUpGachaStepRewardRepository $oprStepUpGachaStepRewardRepository,
        private OprGachaPrizeRepository $oprGachaPrizeRepository,
        private GachaService $gachaService,
        private RewardDelegator $rewardDelegator,
        private MstUnitRepository $mstUnitRepository,
        private MstItemRepository $mstItemRepository,
        private Clock $clock,
    ) {
    }

    /**
     * ステップアップガチャの初期化とバリデーション
     *
     * @param UsrGachaInterface $usrGacha
     * @param string $oprGachaId
     * @param int $playNum
     * @param ?int $clientStepNumber クライアント側で表示されている現在のステップ数
     * @return StepUpGachaState
     * @throws GameException
     */
    public function initializeAndValidate(
        UsrGachaInterface $usrGacha,
        string $oprGachaId,
        int $playNum,
        ?int $clientStepNumber = null
    ): StepUpGachaState {
        // ステップアップガシャ設定取得
        $stepUpGacha = $this->oprStepUpGachaRepository->getByOprGachaId($oprGachaId, true);

        // 現在のステップ情報取得（初回はstep=1, loop_count=0で初期化）
        $currentStepNumber = $usrGacha->getCurrentStepNumber();
        $loopCount = $usrGacha->getLoopCount();

        if (is_null($currentStepNumber)) {
            $currentStepNumber = 1;
            $usrGacha->setCurrentStepNumber($currentStepNumber);
        }
        if (is_null($loopCount)) {
            $loopCount = 0;
            $usrGacha->setLoopCount($loopCount);
        }

        // クライアント側のステップ数とサーバー側のステップ数を照合
        if (!is_null($clientStepNumber) && $clientStepNumber !== $currentStepNumber) {
            throw new GameException(
                ErrorCode::GACHA_STEPUP_STEP_MISMATCH,
                sprintf(
                    'stepup gacha step number mismatch (client:%d, server:%d)',
                    $clientStepNumber,
                    $currentStepNumber
                )
            );
        }

        // 周回上限チェック
        $maxLoopCount = $stepUpGacha->getMaxLoopCount();
        if (!is_null($maxLoopCount) && $loopCount >= $maxLoopCount) {
            throw new GameException(
                ErrorCode::GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED,
                sprintf(
                    'stepup gacha loop count limit exceeded (current:%d, max:%d)',
                    $loopCount,
                    $maxLoopCount
                )
            );
        }

        // 現在のステップ設定を取得
        $oprStepUpGachaStep = $this->oprStepUpGachaStepRepository->getByOprGachaIdStepNumber(
            $oprGachaId,
            $currentStepNumber,
            true
        );

        // 引く回数の検証
        $drawCount = $oprStepUpGachaStep->getDrawCount();
        if ($playNum !== $drawCount) {
            throw new GameException(
                ErrorCode::GACHA_NOT_EXPECTED_PLAY_NUM,
                sprintf(
                    'stepup gacha play_num mismatch (requested:%d, expected:%d)',
                    $playNum,
                    $drawCount
                )
            );
        }

        return new StepUpGachaState(
            $stepUpGacha,
            $oprStepUpGachaStep,
            $currentStepNumber,
            $loopCount
        );
    }

    /**
     * ステップアップガチャ用の抽選BOXを取得
     *
     * @param OprGachaEntity $oprGacha
     * @param OprStepUpGachaStepEntity $oprStepUpGachaStep
     * @return GachaLotteryBox
     * @throws GameException
     */
    public function getLotteryBox(
        OprGachaEntity $oprGacha,
        OprStepUpGachaStepEntity $oprStepUpGachaStep
    ): GachaLotteryBox {
        // ステップアップガチャ用の確定枠抽選BOXを取得（レアリティフィルタリング込み）
        $fixedLotteryBox = $this->getFixedPrizeBox($oprGacha, $oprStepUpGachaStep);

        // 賞品グループIDの優先順位（step設定 > gacha設定）
        $prizeGroupId = $oprStepUpGachaStep->getPrizeGroupId() ?? $oprGacha->getPrizeGroupId();

        // 通常枠の抽選BOX取得
        $regularLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError($prizeGroupId);

        // 共通処理：マスターデータ取得とGachaPrize変換
        return $this->gachaService->convertToGachaLotteryBox($regularLotteryBox, $fixedLotteryBox);
    }

    /**
     * ステップアップガチャのコスト検証
     *
     * @param OprStepUpGachaStepEntity $oprStepUpGachaStep
     * @param CostType $costType
     * @param string|null $costId
     * @param int $costNum
     * @param int $loopCount
     * @return void
     * @throws GameException
     */
    public function validateCost(
        OprStepUpGachaStepEntity $oprStepUpGachaStep,
        CostType $costType,
        ?string $costId,
        int $costNum,
        int $loopCount
    ): void {
        // 初回のみ無料判定（is_first_free + loop_count=0）
        if ($oprStepUpGachaStep->getIsFirstFree() && $loopCount === 0) {
            // 初回無料の場合、コストは0であること
            if ($costType !== CostType::FREE || $costNum !== 0) {
                throw new GameException(
                    ErrorCode::GACHA_UNJUST_COSTS,
                    sprintf(
                        'stepup gacha first free cost mismatch (cost_type:%s, cost_num:%d, expected: Free/0)',
                        $costType->value,
                        $costNum
                    )
                );
            }
            return;
        }

        // cost_type='Free' は常に無料
        if ($oprStepUpGachaStep->getCostType() === CostType::FREE) {
            if ($costType !== CostType::FREE || $costNum !== 0) {
                throw new GameException(
                    ErrorCode::GACHA_UNJUST_COSTS,
                    sprintf(
                        'stepup gacha free cost mismatch (cost_type:%s, cost_num:%d, expected: Free/0)',
                        $costType->value,
                        $costNum
                    )
                );
            }
            return;
        }

        // コストタイプの一致チェック（ステップアップ固有）
        if ($oprStepUpGachaStep->getCostType() !== $costType) {
            throw new GameException(
                ErrorCode::GACHA_UNJUST_COSTS,
                sprintf(
                    'stepup gacha cost type mismatch (cost_type:%s, expected:%s)',
                    $costType->value,
                    $oprStepUpGachaStep->getCostType()->value
                )
            );
        }

        // 基本的なコスト検証（GachaServiceの共通処理を使用）
        $this->gachaService->validateBasicCost(
            $costType,
            $costId,
            $costNum,
            $oprStepUpGachaStep->getCostId(),
            $oprStepUpGachaStep->getCostNum()
        );
    }

    /**
     * ステップアップガチャのステップを進行
     *
     * @param UsrGachaInterface $usrGacha
     * @param OprStepUpGachaEntity $stepUpGacha
     * @return void
     * @throws GameException
     */
    public function progressStep(UsrGachaInterface $usrGacha, OprStepUpGachaEntity $stepUpGacha): void
    {
        $currentStepNumber = $usrGacha->getCurrentStepNumber() ?? 1;
        $loopCount = $usrGacha->getLoopCount() ?? 0;
        $maxStepNumber = $stepUpGacha->getMaxStepNumber();
        $maxLoopCount = $stepUpGacha->getMaxLoopCount();

        // 次のステップへ進行
        if ($currentStepNumber >= $maxStepNumber) {
            // 最終ステップ完了 -> 周回
            $newLoopCount = $loopCount + 1;

            // 最大周回数チェック（nullの場合は無限周回）
            if (!is_null($maxLoopCount) && $newLoopCount > $maxLoopCount) {
                throw new GameException(
                    ErrorCode::GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED,
                    'Step-up gacha max loop count exceeded.'
                );
            }

            $usrGacha->setCurrentStepNumber(1);
            $usrGacha->setLoopCount($newLoopCount);
        } else {
            // 次のステップへ
            $usrGacha->setCurrentStepNumber($currentStepNumber + 1);
        }

        // save()は呼ばない（UseCaseのsaveUsr()で保存される）
    }

    /**
     * ステップアップガチャの確定枠抽選BOXを取得（レアリティフィルタリング込み）
     *
     * @param OprGachaEntity $oprGacha
     * @param OprStepUpGachaStepEntity $oprStepUpGachaStep
     * @return Collection|null
     * @throws GameException
     */
    private function getFixedPrizeBox(
        OprGachaEntity $oprGacha,
        OprStepUpGachaStepEntity $oprStepUpGachaStep
    ): ?Collection {
        $fixedPrizeCount = $oprStepUpGachaStep->getFixedPrizeCount();
        if ($fixedPrizeCount <= 0) {
            return null;
        }

        $fixedPrizeGroupId = $oprStepUpGachaStep->getFixedPrizeGroupId() ?? $oprGacha->getFixedPrizeGroupId();
        if (is_null($fixedPrizeGroupId)) {
            $gachaId = $oprGacha->getId();
            $stepNumber = $oprStepUpGachaStep->getStepNumber();
            throw new GameException(
                ErrorCode::GACHA_BOX_IS_EMPTY,
                "stepup gacha fixed prize group id is null (gacha id {$gachaId}, step {$stepNumber})",
            );
        }

        $fixedLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError($fixedPrizeGroupId);
        if ($fixedLotteryBox->isEmpty()) {
            $gachaId = $oprGacha->getId();
            $stepNumber = $oprStepUpGachaStep->getStepNumber();
            throw new GameException(
                ErrorCode::GACHA_BOX_IS_EMPTY,
                "stepup gacha fixed box is empty (gacha id {$gachaId}, step {$stepNumber})",
            );
        }

        // レアリティ条件でフィルタリング
        $rarityThresholdType = $oprStepUpGachaStep->getFixedPrizeRarityThresholdType();
        if (!is_null($rarityThresholdType)) {
            $fixedLotteryBox = $this->filterPrizesByRarity($fixedLotteryBox, $rarityThresholdType);
            if ($fixedLotteryBox->isEmpty()) {
                throw new GameException(
                    ErrorCode::GACHA_BOX_IS_EMPTY,
                    sprintf(
                        'stepup gacha fixed box is empty after rarity filter (gacha id %s, step %d, rarity %s)',
                        $oprGacha->getId(),
                        $oprStepUpGachaStep->getStepNumber(),
                        $rarityThresholdType->value
                    ),
                );
            }
        }

        return $fixedLotteryBox;
    }

    /**
     * ステップアップガチャの各ステップの排出率情報を取得
     *
     * @param OprGachaEntity $oprGacha
     * @return Collection
     * @throws GameException
     */
    public function getPrizes(OprGachaEntity $oprGacha): Collection
    {
        if (!$oprGacha->isStepUp()) {
            return collect();
        }
        $stepUpGacha = $this->oprStepUpGachaRepository->findByOprGachaId($oprGacha->getId());

        if (is_null($stepUpGacha)) {
            throw new GameException(
                ErrorCode::GACHA_STEPUP_NOT_FOUND,
                "ステップアップガシャのマスターデータが見つかりません: {$oprGacha->getId()}"
            );
        }

        // 全ステップの設定を取得
        $oprStepUpGachaSteps = $this->oprStepUpGachaStepRepository->getListByOprGachaId($oprGacha->getId());

        return $oprStepUpGachaSteps->map(function ($oprStepUpGachaStep) use ($oprGacha) {
            // 賞品グループIDの優先順位（step設定 > gacha設定）
            $prizeGroupId = $oprStepUpGachaStep->getPrizeGroupId() ?? $oprGacha->getPrizeGroupId();

            // 通常枠の抽選BOX取得
            $regularLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError($prizeGroupId);

            // 確定枠の抽選BOX取得とレアリティフィルタリング
            $fixedLotteryBox = $this->getFixedPrizeBox($oprGacha, $oprStepUpGachaStep);

            // マスターデータ取得とGachaPrize変換
            $gachaLotteryBox = $this->gachaService->convertToGachaLotteryBox($regularLotteryBox, $fixedLotteryBox);

            // 確定枠のレアリティ別確率と賞品リストを計算
            $fixedBox = $gachaLotteryBox->getFixedLotteryBox() ?? collect();
            $rarityProbabilities = $this->gachaService->generateRarityProbability($fixedBox);
            $probabilityGroups = $this->gachaService->generatePrizeProbabilityByPrize($fixedBox);

            return new StepUpGachaPrizeInfoData(
                $oprStepUpGachaStep->getStepNumber(),
                $oprStepUpGachaStep->getDrawCount(),
                $oprStepUpGachaStep->getFixedPrizeCount(),
                $oprStepUpGachaStep->getFixedPrizeRarityThresholdType()?->value,
                $rarityProbabilities,
                $probabilityGroups
            );
        });
    }

    /**
     * ステップアップガシャのおまけ報酬を追加
     *
     * @param string $oprGachaId
     * @param int $stepNumber
     * @param int $loopCount
     * @return void
     */
    public function addStepRewards(
        string $oprGachaId,
        int $stepNumber,
        int $loopCount
    ): void {
        // おまけ報酬を取得
        $stepRewardEntities = $this->oprStepUpGachaStepRewardRepository->getRewardsForStep(
            $oprGachaId,
            $stepNumber,
            $loopCount
        );

        // StepUpGachaStepRewardオブジェクトに変換
        $stepRewards = $this->makeStepRewards($stepRewardEntities, $oprGachaId, $stepNumber, $loopCount);

        // RewardDelegatorに追加
        $this->rewardDelegator->addRewards($stepRewards);
    }

    /**
     * EntityコレクションをStepUpGachaStepRewardオブジェクトに変換
     *
     * @param Collection $stepRewardEntities
     * @param string $oprGachaId
     * @param int $stepNumber
     * @param int $loopCount
     * @return Collection<StepUpGachaStepReward>
     */
    private function makeStepRewards(
        Collection $stepRewardEntities,
        string $oprGachaId,
        int $stepNumber,
        int $loopCount
    ): Collection {
        return $stepRewardEntities->map(function ($entity) use ($oprGachaId, $stepNumber, $loopCount) {
            return new StepUpGachaStepReward(
                $entity->getResourceType(),
                $entity->getResourceId(),
                $entity->getResourceAmount(),
                $oprGachaId,
                $stepNumber,
                $loopCount,
            );
        });
    }

    /**
     * レアリティ条件でフィルタリング（ステップアップガチャ専用）
     *
     * @param Collection $prizes
     * @param RarityType $rarityThresholdType
     * @return Collection
     */
    private function filterPrizesByRarity(Collection $prizes, RarityType $rarityThresholdType): Collection
    {
        // レアリティの優先順位を定義（N < R < SR < SSR < UR）
        $rarityOrder = [
            RarityType::N->value => 1,
            RarityType::R->value => 2,
            RarityType::SR->value => 3,
            RarityType::SSR->value => 4,
            RarityType::UR->value => 5,
        ];

        $thresholdValue = $rarityOrder[$rarityThresholdType->value];

        return $prizes->filter(function (OprGachaPrizeEntity $prize) use ($thresholdValue, $rarityOrder) {
            // 賞品のレアリティを取得
            $prizeRarity = null;
            switch ($prize->getResourceType()) {
                case RewardType::UNIT:
                    $unit = $this->mstUnitRepository->getById($prize->getResourceId());
                    $prizeRarity = $unit?->getRarity();
                    break;
                case RewardType::ITEM:
                    $item = $this->mstItemRepository->getActiveItemById($prize->getResourceId(), $this->clock->now());
                    $prizeRarity = $item?->getRarity();
                    break;
                case RewardType::COIN:
                    $prizeRarity = RarityType::R->value; // コインは最低レアリティのR
                    break;
            }

            if (is_null($prizeRarity)) {
                return false;
            }

            $prizeRarityValue = $rarityOrder[$prizeRarity] ?? 0;
            return $prizeRarityValue >= $thresholdValue;
        });
    }
}
