<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Repositories\MstInGameSpecialRuleRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Stage\Constants\StageConstant;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use Carbon\CarbonImmutable;

class StageContinueService
{
    public function __construct(
        private UsrStageSessionRepository $usrStageSessionRepository,
        private MstInGameSpecialRuleRepository $mstInGameSpecialRuleRepository,
        // Service
        private StageService $stageService,
        private MstConfigService $mstConfigService,
    ) {
    }

    /**
     * コンティニュー不可かどうか判定
     *
     * @return bool true: コンティニュー不可, false: コンティニュー可能
     */
    public function isNotContinue(string $mstStageId, CarbonImmutable $now): bool
    {
        $mstInGameSpecialRules = $this->mstInGameSpecialRuleRepository
            ->getByContentTypeAndTargetIdAndRuleType(
                InGameContentType::STAGE,
                $mstStageId,
                InGameSpecialRuleType::NO_CONTINUE,
                $now,
            );

        return $mstInGameSpecialRules->isNotEmpty();
    }

    /**
     * ステージのコンティニューが可能かチェックする
     * @throws GameException
     */
    public function checkStageEventContinue(
        QuestType $questType,
        string $mstStageId,
        CarbonImmutable $now,
    ): void {
        if ($questType !== QuestType::EVENT) {
            // イベントクエスト以外はコンティニュー可
            return;
        }

        $throwException = function () use ($mstStageId) {
            throw new GameException(
                ErrorCode::STAGE_CAN_NOT_CONTINUE,
                'stage can not continue (mst_stage_id: ' . $mstStageId . ')'
            );
        };

        if ($this->stageService->isSpeedAttack($mstStageId, $now)) {
            // スピードアタックはコンティニュー不可
            $throwException();
        }

        if ($this->isNotContinue($mstStageId, $now)) {
            // コンティニュー不可
            $throwException();
        }
    }

    /**
     * ステージを続行できるか確認
     *
     * @param UsrStageSessionInterface $usrStageSession
     * @return void
     * @throws GameException
     */
    public function checkStageContinueLimit(UsrStageSessionInterface $usrStageSession): void
    {
        if ($usrStageSession->getContinueCount() >= StageConstant::CONTINUE_MAX_COUNT) {
            throw new GameException(
                ErrorCode::STAGE_CONTINUE_LIMIT,
                "continue count limit (continue_count: " . $usrStageSession->getContinueCount() . ")"
            );
        }
    }

    /**
     * 広告視聴でステージを続行できるか確認
     *
     * @param UsrStageSessionInterface $usrStageSession
     * @throws GameException
     */
    public function checkDailyStageContinueAdLimit(
        UsrStageSessionInterface $usrStageSession
    ): void {
        $configAdContinueMaxCount = $this->mstConfigService->getAdContinueMaxCount();
        if ($usrStageSession->getDailyContinueAdCount() >= $configAdContinueMaxCount) {
            throw new GameException(
                ErrorCode::STAGE_CONTINUE_LIMIT,
                "continue ad count limit (daily_continue_ad_count: "
                . $usrStageSession->getDailyContinueAdCount() . ")"
            );
        }
    }

    /**
     * ステージコンティニューする
     *
     * @param UsrStageSessionInterface $usrStageSession
     */
    public function continue(UsrStageSessionInterface $usrStageSession): void
    {
        $usrStageSession->incrementContinueCount();
        $this->usrStageSessionRepository->syncModel($usrStageSession);
    }

    /**
     * 広告視聴でステージコンティニューする
     *
     * @param UsrStageSessionInterface $usrStageSession
     */
    public function continueAd(UsrStageSessionInterface $usrStageSession): void
    {
        $usrStageSession->incrementContinueCount();
        $usrStageSession->incrementDailyContinueAdCount();
        $this->usrStageSessionRepository->syncModel($usrStageSession);
    }
}
