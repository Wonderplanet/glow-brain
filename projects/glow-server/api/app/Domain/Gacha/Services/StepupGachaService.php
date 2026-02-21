<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Entities\GachaLotteryBox;
use App\Domain\Gacha\Entities\StepupGachaState;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Resource\Entities\Rewards\StepupGachaStepReward;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity;
use App\Domain\Resource\Mst\Entities\OprStepupGachaEntity;
use App\Domain\Resource\Mst\Entities\OprStepupGachaStepEntity;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaPrizeRepository;
use App\Domain\Resource\Mst\Repositories\OprStepupGachaRepository;
use App\Domain\Resource\Mst\Repositories\OprStepupGachaStepRepository;
use App\Domain\Resource\Mst\Repositories\OprStepupGachaStepRewardRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Http\Responses\Data\StepupGachaPrizeInfoData;
use Illuminate\Support\Collection;

/**
 * ステップアップガチャ専用サービス
 */
class StepupGachaService
{
    public function __construct(
        private OprStepupGachaRepository $oprStepupGachaRepository,
        private OprStepupGachaStepRepository $oprStepupGachaStepRepository,
        private OprStepupGachaStepRewardRepository $oprStepupGachaStepRewardRepository,
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
     * @param ?int $clientStepNumber クライアント側で表示されている現在のステップ数
     * @return StepupGachaState
     * @throws GameException
     */
    public function initializeAndValidate(
        UsrGachaInterface $usrGacha,
        string $oprGachaId,
        ?int $clientStepNumber = null
    ): StepupGachaState {
        // ステップアップガシャ設定取得
        $stepupGacha = $this->oprStepupGachaRepository->getByOprGachaId($oprGachaId, true);

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
        $maxLoopCount = $stepupGacha->getMaxLoopCount();
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
        $oprStepupGachaStep = $this->oprStepupGachaStepRepository->getByOprGachaIdStepNumber(
            $oprGachaId,
            $currentStepNumber,
            true
        );

        return new StepupGachaState(
            $stepupGacha,
            $oprStepupGachaStep,
            $currentStepNumber,
            $loopCount
        );
    }

    /**
     * playNumのバリデーションと解決
     *
     * costTypeがFREEの場合はクライアントからplayNumが送られないため、ステップ定義のdrawCountを返す。
     * それ以外の場合はリクエストのplayNumとステップ定義のdrawCountが一致するか検証する。
     *
     * @param OprStepupGachaStepEntity $oprStepupGachaStep
     * @param int $playNum
     * @param CostType $costType
     * @return int 解決済みのplayNum
     * @throws GameException
     */
    public function validateAndResolvePlayNum(
        OprStepupGachaStepEntity $oprStepupGachaStep,
        int $playNum,
        CostType $costType,
    ): int {
        $drawCount = $oprStepupGachaStep->getDrawCount();

        // FREEの場合はステップのdrawCountをそのまま使用（validateCostで検証済み）
        if ($costType === CostType::FREE) {
            return $drawCount;
        }

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

        return $playNum;
    }

    /**
     * ステップアップガチャ用の抽選BOXを取得
     *
     * @param OprGachaEntity $oprGacha
     * @param OprStepupGachaStepEntity $oprStepupGachaStep
     * @return GachaLotteryBox
     * @throws GameException
     */
    public function getLotteryBox(
        OprGachaEntity $oprGacha,
        OprStepupGachaStepEntity $oprStepupGachaStep
    ): GachaLotteryBox {
        // ステップアップガチャ用の確定枠抽選BOXを取得（レアリティフィルタリング込み）
        $fixedLotteryBox = $this->getFixedPrizeBox($oprGacha, $oprStepupGachaStep);

        // 賞品グループIDの優先順位（step設定 > gacha設定）
        $prizeGroupId = $oprStepupGachaStep->getPrizeGroupId() ?? $oprGacha->getPrizeGroupId();

        // 通常枠の抽選BOX取得
        $regularLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError($prizeGroupId);

        // 共通処理：マスターデータ取得とGachaPrize変換
        return $this->gachaService->convertToGachaLotteryBox($regularLotteryBox, $fixedLotteryBox);
    }

    /**
     * ステップアップガチャのコスト検証
     *
     * ステップ定義のコストと一致する場合はステップ定義で検証する。
     * 一致しない場合は opr_gacha_use_resources テーブルからフォールバック検索を行い、
     * 補填チケット等の代替コストでの実行を許可する。
     *
     * @param OprGachaEntity $oprGacha
     * @param OprStepupGachaStepEntity $oprStepupGachaStep
     * @param CostType $costType
     * @param string|null $costId
     * @param int $costNum
     * @param int $loopCount
     * @return void
     * @throws GameException
     */
    public function validateCost(
        OprGachaEntity $oprGacha,
        OprStepupGachaStepEntity $oprStepupGachaStep,
        CostType $costType,
        ?string $costId,
        int $costNum,
        int $loopCount,
    ): void {
        // 初回のみ無料判定（is_first_free + loop_count=0）
        if ($oprStepupGachaStep->getIsFirstFree() && $loopCount === 0) {
            // 初回無料の場合、コストタイプがFREEであること
            if ($costType !== CostType::FREE) {
                throw new GameException(
                    ErrorCode::GACHA_UNJUST_COSTS,
                    sprintf(
                        'stepup gacha first free cost mismatch (cost_type:%s, expected: Free)',
                        $costType->value
                    )
                );
            }
            return;
        }

        // cost_type='Free' は常に無料
        if ($oprStepupGachaStep->getCostType() === CostType::FREE) {
            if ($costType !== CostType::FREE) {
                throw new GameException(
                    ErrorCode::GACHA_UNJUST_COSTS,
                    sprintf(
                        'stepup gacha free cost mismatch (cost_type:%s, expected: Free)',
                        $costType->value
                    )
                );
            }
            return;
        }

        // ステップ定義のコストタイプと一致する場合はステップ定義で検証
        if ($oprStepupGachaStep->getCostType() === $costType) {
            $this->gachaService->validateBasicCost(
                $costType,
                $costId,
                $costNum,
                $oprStepupGachaStep->getCostId(),
                $oprStepupGachaStep->getCostNum()
            );
            return;
        }

        // ステップ定義と異なるコストタイプの場合、opr_gacha_use_resources からフォールバック検索
        // （補填チケット等の代替コストでの実行を許可する）
        $this->gachaService->validateCost(
            $oprGacha,
            $oprStepupGachaStep->getDrawCount(),
            $costId,
            $costNum,
            $costType
        );
    }

    /**
     * ステップアップガチャのステップを進行
     *
     * @param UsrGachaInterface $usrGacha
     * @param OprStepupGachaEntity $stepupGacha
     * @return void
     * @throws GameException
     */
    public function progressStep(UsrGachaInterface $usrGacha, OprStepupGachaEntity $stepupGacha): void
    {
        $currentStepNumber = $usrGacha->getCurrentStepNumber() ?? 1;
        $loopCount = $usrGacha->getLoopCount() ?? 0;
        $maxStepNumber = $stepupGacha->getMaxStepNumber();
        $maxLoopCount = $stepupGacha->getMaxLoopCount();

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
     * @param OprStepupGachaStepEntity $oprStepupGachaStep
     * @return Collection|null
     * @throws GameException
     */
    private function getFixedPrizeBox(
        OprGachaEntity $oprGacha,
        OprStepupGachaStepEntity $oprStepupGachaStep
    ): ?Collection {
        $fixedPrizeCount = $oprStepupGachaStep->getFixedPrizeCount();
        if ($fixedPrizeCount <= 0) {
            return null;
        }

        $fixedPrizeGroupId = $oprStepupGachaStep->getFixedPrizeGroupId() ?? $oprGacha->getFixedPrizeGroupId();
        if (is_null($fixedPrizeGroupId)) {
            $gachaId = $oprGacha->getId();
            $stepNumber = $oprStepupGachaStep->getStepNumber();
            throw new GameException(
                ErrorCode::GACHA_BOX_IS_EMPTY,
                "stepup gacha fixed prize group id is null (gacha id {$gachaId}, step {$stepNumber})",
            );
        }

        $fixedLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError($fixedPrizeGroupId);
        if ($fixedLotteryBox->isEmpty()) {
            $gachaId = $oprGacha->getId();
            $stepNumber = $oprStepupGachaStep->getStepNumber();
            throw new GameException(
                ErrorCode::GACHA_BOX_IS_EMPTY,
                "stepup gacha fixed box is empty (gacha id {$gachaId}, step {$stepNumber})",
            );
        }

        // レアリティ条件でフィルタリング
        $rarityThresholdType = $oprStepupGachaStep->getFixedPrizeRarityThresholdType();
        if (!is_null($rarityThresholdType)) {
            $fixedLotteryBox = $this->filterPrizesByRarity($fixedLotteryBox, $rarityThresholdType);
            if ($fixedLotteryBox->isEmpty()) {
                throw new GameException(
                    ErrorCode::GACHA_BOX_IS_EMPTY,
                    sprintf(
                        'stepup gacha fixed box is empty after rarity filter (gacha id %s, step %d, rarity %s)',
                        $oprGacha->getId(),
                        $oprStepupGachaStep->getStepNumber(),
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
        if (!$oprGacha->isStepup()) {
            return collect();
        }
        $stepupGacha = $this->oprStepupGachaRepository->findByOprGachaId($oprGacha->getId());

        if (is_null($stepupGacha)) {
            throw new GameException(
                ErrorCode::GACHA_STEPUP_NOT_FOUND,
                "ステップアップガシャのマスターデータが見つかりません: {$oprGacha->getId()}"
            );
        }

        // 全ステップの設定を取得
        $oprStepupGachaSteps = $this->oprStepupGachaStepRepository->getListByOprGachaId($oprGacha->getId());

        return $oprStepupGachaSteps->map(function ($oprStepupGachaStep) use ($oprGacha) {
            // 賞品グループIDの優先順位（step設定 > gacha設定）
            $prizeGroupId = $oprStepupGachaStep->getPrizeGroupId() ?? $oprGacha->getPrizeGroupId();

            // 通常枠の抽選BOX取得
            $regularLotteryBox = $this->oprGachaPrizeRepository->getByGroupIdWithError($prizeGroupId);

            // 確定枠の抽選BOX取得とレアリティフィルタリング
            $fixedLotteryBox = $this->getFixedPrizeBox($oprGacha, $oprStepupGachaStep);

            // マスターデータ取得とGachaPrize変換
            $gachaLotteryBox = $this->gachaService->convertToGachaLotteryBox($regularLotteryBox, $fixedLotteryBox);

            // 確定枠のレアリティ別確率と賞品リストを計算
            $fixedBox = $gachaLotteryBox->getFixedLotteryBox() ?? collect();
            $rarityProbabilities = $this->gachaService->generateRarityProbability($fixedBox);
            $probabilityGroups = $this->gachaService->generatePrizeProbabilityByPrize($fixedBox);

            return new StepupGachaPrizeInfoData(
                $oprStepupGachaStep->getStepNumber(),
                $oprStepupGachaStep->getDrawCount(),
                $oprStepupGachaStep->getFixedPrizeCount(),
                $oprStepupGachaStep->getFixedPrizeRarityThresholdType()?->value,
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
        $stepRewardEntities = $this->oprStepupGachaStepRewardRepository->getRewardsForStep(
            $oprGachaId,
            $stepNumber,
            $loopCount
        );

        // StepupGachaStepRewardオブジェクトに変換
        $stepRewards = $this->makeStepRewards($stepRewardEntities, $oprGachaId, $stepNumber, $loopCount);

        // RewardDelegatorに追加
        $this->rewardDelegator->addRewards($stepRewards);
    }

    /**
     * EntityコレクションをStepupGachaStepRewardオブジェクトに変換
     *
     * @param Collection $stepRewardEntities
     * @param string $oprGachaId
     * @param int $stepNumber
     * @param int $loopCount
     * @return Collection<StepupGachaStepReward>
     */
    private function makeStepRewards(
        Collection $stepRewardEntities,
        string $oprGachaId,
        int $stepNumber,
        int $loopCount
    ): Collection {
        return $stepRewardEntities->map(function ($entity) use ($oprGachaId, $stepNumber, $loopCount) {
            return new StepupGachaStepReward(
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
