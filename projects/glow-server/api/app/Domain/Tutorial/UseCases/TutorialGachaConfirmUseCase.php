<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Delegators\GachaDelegator;
use App\Domain\Gacha\Entities\GachaResultData;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Mst\Repositories\OprGachaUseResourceRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Repositories\UsrTutorialGachaRepository;
use App\Domain\Tutorial\Services\TutorialGachaService;
use App\Domain\Tutorial\Services\TutorialLogService;
use App\Domain\Tutorial\Services\TutorialStatusService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\TutorialGachaConfirmResultData;

class TutorialGachaConfirmUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MstRepository
        private OprGachaUseResourceRepository $oprGachaUseResourceRepository,
        // UsrRepository
        private UsrTutorialGachaRepository $usrTutorialGachaRepository,
        // Service
        private TutorialGachaService $tutorialGachaService,
        private TutorialLogService $tutorialLogService,
        private TutorialStatusService $tutorialStatusService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        // Common
        private Clock $clock,
        // Delegator
        private GachaDelegator $gachaDelegator,
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
    ) {
    }

    public function exec(CurrentUser $user, int $platform): TutorialGachaConfirmResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $usrTutorialGacha = $this->usrTutorialGachaRepository->get($usrUserId);

        $this->tutorialGachaService->validateGachaConfirm($usrTutorialGacha);

        $gachaResultData = GachaResultData::fromArray($usrTutorialGacha->getGachaResultJson());

        // 報酬配布リストに追加
        $gachaRewards = $this->tutorialGachaService->makeGachaRewardsByGachaResultData(
            $gachaResultData,
        );
        $this->rewardDelegator->addRewards($gachaRewards);

        // 確定済ステータスへ更新
        $usrTutorialGacha->confirm($now);
        $this->usrTutorialGachaRepository->syncModel($usrTutorialGacha);

        // チュートリアルステータスを更新する
        $tutorialStatus = TutorialFunctionName::GACHA_CONFIRMED->value;
        $this->tutorialStatusService->updateTutorialStatus(
            $usrUserId,
            $now,
            $tutorialStatus,
            $platform,
        );

        // ログ保存
        $this->tutorialLogService->sendGachaConfirmLog(
            $usrUserId,
            $gachaResultData,
        );

        $oprGachaId = $gachaResultData->getOprGachaId();
        /** @var \App\Domain\Resource\Mst\Entities\OprGachaUseResourceEntity|null $oprGachaUseResource */
        $oprGachaUseResource = $this->oprGachaUseResourceRepository->getByOprGachaId($oprGachaId)->first();

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $platform,
            $now,
            $oprGachaId,
            $oprGachaUseResource
        ) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

            // ガチャ履歴のキャッシュ保存
            $gachaRewards = $this->rewardDelegator->getSentRewards(GachaReward::class);
            $this->gachaDelegator->addGachaHistory(
                $usrUserId,
                $oprGachaId,
                // oprGachaUseResourceが存在しない場合も考慮
                $oprGachaUseResource?->getCostType()?->value ?? CostType::DIAMOND->value,
                $oprGachaUseResource?->getCostId() ?? null,
                $oprGachaUseResource?->getCostNum() ?? 0,
                $oprGachaUseResource?->getDrawCount() ?? $gachaRewards->count(),
                $now,
                $gachaRewards
            );
        });

        return new TutorialGachaConfirmResultData(
            $tutorialStatus,
            $this->rewardDelegator->getSentRewards(GachaReward::class),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->makeUsrParameterData(
                $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)
            ),
        );
    }
}
