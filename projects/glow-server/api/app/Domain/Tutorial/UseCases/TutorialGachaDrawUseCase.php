<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Delegators\GachaDelegator;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Repositories\OprGachaRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Tutorial\Repositories\UsrTutorialGachaRepository;
use App\Domain\Tutorial\Services\TutorialGachaService;
use App\Http\Responses\ResultData\TutorialGachaDrawResultData;

class TutorialGachaDrawUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MstRepository
        private OprGachaRepository $oprGachaRepository,
        // UsrRepository
        private UsrTutorialGachaRepository $usrTutorialGachaRepository,
        // Service
        private TutorialGachaService $tutorialGachaService,
        // Common
        private Clock $clock,
        // Delegator
        private GachaDelegator $gachaDelegator,
        private RewardDelegator $rewardDelegator,
    ) {
    }

    public function exec(CurrentUser $user): TutorialGachaDrawResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        /** @var OprGachaEntity $oprGacha */
        $oprGacha = $this->oprGachaRepository
            ->getByGachaType(GachaType::TUTORIAL, isThrowError: true)
            ->first();

        $usrTutorialGacha = $this->usrTutorialGachaRepository->getOrCreate($usrUserId);

        $this->tutorialGachaService->validateGachaDraw($usrTutorialGacha);

        $gachaResultData = $this->gachaDelegator->drawTutorial(
            $usrUserId,
            $now,
            $oprGacha,
            playNum: $oprGacha->getMultiDrawCount(),
            costType: CostType::DIAMOND,
        );

        // ガシャ排出結果そのままを記録する
        $this->tutorialGachaService->overwriteGachaTemporaryResult(
            $usrTutorialGacha,
            $gachaResultData,
        );

        // キャラ重複排出した場合、実際に配布するリソース(キャラのかけらなど)へ変換した状態でレスポンスする
        $gachaRewards = $this->gachaDelegator->makeGachaRewardByGachaBoxes(
            $gachaResultData->getResult(),
            $oprGacha->getId()
        );
        $gachaRewards = $this->rewardDelegator->getConvertedRewardsWithoutSend(
            $usrUserId,
            $now,
            $gachaRewards,
        );

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new TutorialGachaDrawResultData(
            $gachaRewards,
        );
    }
}
