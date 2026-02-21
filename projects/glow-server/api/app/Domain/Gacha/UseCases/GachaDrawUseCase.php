<?php

declare(strict_types=1);

namespace App\Domain\Gacha\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Entities\GachaDrawRequest;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Factories\GachaServiceFactory;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Entities\Rewards\StepupGachaStepReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\GachaDrawResultData;

class GachaDrawUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Factories
        private GachaServiceFactory $gachaServiceFactory,
        // Delegators
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
        // Services
        private GachaService $gachaService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $usr
     * @param string $oprGachaId
     * @param int $drewCount
     * @param int $playNum 排出数(例：1連、10連, N連)
     * @param ?string $costId
     * @param int $costNum
     * @param int $platform
     * @param string $billingPlatform
     * @param CostType $costType
     * @param ?int $currentStepNumber クライアント側で表示されている現在のステップ数（ステップアップガチャのみ）
     *
     * @return GachaDrawResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $usr,
        string $oprGachaId,
        int $drewCount,
        int $playNum,
        ?string $costId,
        int $costNum,
        int $platform,
        string $billingPlatform,
        CostType $costType,
        ?int $currentStepNumber = null
    ): GachaDrawResultData {
        $now = $this->clock->now();

        // ガシャ情報取得
        $oprGacha = $this->gachaService->getOprGacha($oprGachaId);

        // リクエストオブジェクトを生成
        $request = new GachaDrawRequest(
            $usr,
            $oprGacha,
            $drewCount,
            $playNum,
            $costType,
            $costId,
            $costNum,
            $platform,
            $billingPlatform,
            $now,
            $currentStepNumber,
        );

        // ガチャタイプに応じた抽選サービスを取得
        $gachaDrawService = $this->gachaServiceFactory->getGachaDrawService($oprGacha->getGachaType()->value);

        // ガチャ抽選を実行
        $gachaDrawResult = $gachaDrawService->draw($request);

        // トランザクションで囲んでDB更新処理を実行する
        $this->applyUserTransactionChanges(function () use (
            $usr,
            $now,
            $platform,
            $oprGachaId,
            $costType,
            $costId,
            $costNum,
            $playNum,
            $gachaDrawResult,
            $gachaDrawService,
        ) {
            // リソース消費を実行（DrawServiceを通じて内部のGachaServiceのexecConsumeResourceを呼ぶ）
            $gachaDrawService->execConsumeResource($gachaDrawResult->getLogGachaAction());

            // 報酬配布実行（ガシャ報酬 + おまけ報酬）
            $this->rewardDelegator->sendRewards($usr->getUsrUserId(), $platform, $now);

            // ガチャ履歴のキャッシュ保存
            $gachaRewards = $this->rewardDelegator->getSentRewards(GachaReward::class);
            $stepRewards = $this->rewardDelegator->getSentRewards(StepupGachaStepReward::class);
            $this->gachaService->addGachaHistory(
                $usr->getUsrUserId(),
                $oprGachaId,
                $costType->value,
                $costId,
                $costNum,
                $playNum,
                $now,
                $gachaRewards,
                $gachaDrawResult->getCurrentStepNumber(),
                $gachaDrawResult->getLoopCount(),
                $stepRewards->isNotEmpty() ? $stepRewards : null
            );
        });

        // レスポンス用データ作成
        $gachaRewards = $this
            ->rewardDelegator
            ->getSentRewards(GachaReward::class)
            ->sortBy(fn(GachaReward $reward) => $reward->getSortOrder())
            ->values();

        // おまけ報酬をstepRewardsとして設定
        $stepRewards = $this
            ->rewardDelegator
            ->getSentRewards(StepupGachaStepReward::class);

        return new GachaDrawResultData(
            $gachaRewards,
            $stepRewards,
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->makeUsrParameterData(
                $this->userDelegator->getUsrUserParameterByUsrUserId($usr->getUsrUserId())
            ),
            $gachaDrawResult->getUsrGacha(),
            $gachaDrawResult->getUsrGachaUppers(),
        );
    }
}
