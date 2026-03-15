<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Gacha\Entities\GachaLotteryBox;
use App\Domain\Gacha\Entities\StepupGachaState;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Resource\Entities\Rewards\StepupGachaStepReward;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Entities\OprStepupGachaEntity;
use App\Domain\Resource\Mst\Entities\OprStepupGachaStepEntity;
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
        // ステップアップガチャ用の確定枠抽選BOXを取得
        $fixedLotteryBox = $this->getFixedPrizeBox($oprGacha, $oprStepupGachaStep);

        // 賞品グループIDの優先順位（step設定 > gacha設定）
        $prizeGroupId = StringUtil::isSpecified($oprStepupGachaStep->getPrizeGroupId())
            ? $oprStepupGachaStep->getPrizeGroupId()
            : $oprGacha->getPrizeGroupId();

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
     * ステップアップガチャの確定枠抽選BOXを取得
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

        $fixedPrizeGroupId = StringUtil::isSpecified($oprStepupGachaStep->getFixedPrizeGroupId())
            ? $oprStepupGachaStep->getFixedPrizeGroupId()
            : $oprGacha->getFixedPrizeGroupId();
        if (!StringUtil::isSpecified($fixedPrizeGroupId)) {
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

        // エラーチェックとしてステップアップガチャの基本情報を取得
        $this->oprStepupGachaRepository->getByOprGachaId($oprGacha->getId(), true);

        // 全ステップの設定を取得
        $oprStepupGachaSteps = $this->oprStepupGachaStepRepository->getListByOprGachaId($oprGacha->getId());

        // 各ステップで使用するグループIDを収集
        $allGroupIds = collect();
        $oprStepupGachaSteps->each(function ($step) use ($oprGacha, $allGroupIds) {
            $prizeGroupId = StringUtil::isSpecified($step->getPrizeGroupId())
                ? $step->getPrizeGroupId()
                : $oprGacha->getPrizeGroupId();
            $allGroupIds->push($prizeGroupId);

            if ($step->getFixedPrizeCount() > 0) {
                $fixedPrizeGroupId = StringUtil::isSpecified($step->getFixedPrizeGroupId())
                    ? $step->getFixedPrizeGroupId()
                    : $oprGacha->getFixedPrizeGroupId();
                if (!StringUtil::isSpecified($fixedPrizeGroupId)) {
                    throw new GameException(
                        ErrorCode::GACHA_BOX_IS_EMPTY,
                        sprintf(
                            'stepup gacha fixed prize group id is null (gacha id %s, step %d)',
                            $oprGacha->getId(),
                            $step->getStepNumber()
                        )
                    );
                }
                $allGroupIds->push($fixedPrizeGroupId);
            }
        });
        $uniqueGroupIds = $allGroupIds->unique()->values();

        // 一括でマスタデータを取得しバリデーション
        $allLotteryBoxes = $this->oprGachaPrizeRepository->getByGroupIds($uniqueGroupIds);
        foreach ($uniqueGroupIds as $groupId) {
            if (!isset($allLotteryBoxes[$groupId]) || $allLotteryBoxes[$groupId]->isEmpty()) {
                throw new GameException(
                    ErrorCode::GACHA_BOX_IS_EMPTY,
                    sprintf('stepup gacha prize box is empty (gacha id %s, group id %s)', $oprGacha->getId(), $groupId),
                );
            }
        }

        // 各ステップごとに確率データを計算
        return $oprStepupGachaSteps->map(function ($oprStepupGachaStep) use ($oprGacha, $allLotteryBoxes) {
            $prizeGroupId = StringUtil::isSpecified($oprStepupGachaStep->getPrizeGroupId())
                ? $oprStepupGachaStep->getPrizeGroupId()
                : $oprGacha->getPrizeGroupId();
            $regularLotteryBox = $allLotteryBoxes[$prizeGroupId];

            $fixedLotteryBox = null;
            if ($oprStepupGachaStep->getFixedPrizeCount() > 0) {
                $fixedPrizeGroupId = StringUtil::isSpecified($oprStepupGachaStep->getFixedPrizeGroupId())
                    ? $oprStepupGachaStep->getFixedPrizeGroupId()
                    : $oprGacha->getFixedPrizeGroupId();
                $fixedLotteryBox = $allLotteryBoxes[$fixedPrizeGroupId];
            }

            $gachaLotteryBox = $this->gachaService->convertToGachaLotteryBox($regularLotteryBox, $fixedLotteryBox);

            $regularBox = $gachaLotteryBox->getRegularLotteryBox();
            $rarityProbabilities = $this->gachaService->generateRarityProbability($regularBox);
            $probabilityGroups = $this->gachaService->generatePrizeProbabilityByPrize($regularBox);

            $fixedRarityProbabilities = collect();
            $fixedProbabilityGroups = collect();
            if ($oprStepupGachaStep->getFixedPrizeCount() > 0) {
                $fixedBox = $gachaLotteryBox->getFixedLotteryBox();
                $fixedRarityProbabilities = $this->gachaService->generateRarityProbability($fixedBox);
                $fixedProbabilityGroups = $this->gachaService->generatePrizeProbabilityByPrize($fixedBox);
            }

            return new StepupGachaPrizeInfoData(
                $oprStepupGachaStep->getStepNumber(),
                $oprStepupGachaStep->getDrawCount(),
                $oprStepupGachaStep->getFixedPrizeCount(),
                $rarityProbabilities,
                $probabilityGroups,
                $fixedRarityProbabilities,
                $fixedProbabilityGroups,
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
}
