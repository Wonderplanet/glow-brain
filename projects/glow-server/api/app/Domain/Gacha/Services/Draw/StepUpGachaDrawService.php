<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services\Draw;

use App\Domain\Common\Entities\Clock;
use App\Domain\Gacha\Entities\GachaDrawRequest;
use App\Domain\Gacha\Entities\GachaDrawResult;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\Gacha\Repositories\LogGachaActionRepository;
use App\Domain\Gacha\Services\GachaLogService;
use App\Domain\Gacha\Services\GachaMissionTriggerService;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Gacha\Services\StepUpGachaService;
use App\Domain\Reward\Delegators\RewardDelegator;

/**
 * ステップアップガチャ抽選サービス
 */
class StepUpGachaDrawService extends GachaDrawService
{
    public function __construct(
        GachaService $gachaService,
        GachaMissionTriggerService $gachaMissionTriggerService,
        GachaLogService $gachaLogService,
        RewardDelegator $rewardDelegator,
        Clock $clock,
        LogGachaActionRepository $logGachaActionRepository,
        private StepUpGachaService $stepUpGachaService,
    ) {
        parent::__construct(
            $gachaService,
            $gachaMissionTriggerService,
            $gachaLogService,
            $rewardDelegator,
            $clock,
            $logGachaActionRepository,
        );
    }

    /**
     * ステップアップガチャの抽選を実行する
     *
     * @param GachaDrawRequest $request
     * @return GachaDrawResult
     * @throws \Throwable
     */
    public function draw(GachaDrawRequest $request): GachaDrawResult
    {
        $usr = $request->getUsr();
        $oprGacha = $request->getOprGacha();
        $drewCount = $request->getDrewCount();
        $playNum = $request->getPlayNum();
        $costType = $request->getCostType();
        $costId = $request->getCostId();
        $costNum = $request->getCostNum();
        $platform = $request->getPlatform();
        $billingPlatform = $request->getBillingPlatform();
        $currentStepNumber = $request->getCurrentStepNumber();

        // ガシャ情報取得
        $usrGacha = $this->gachaService->getUsrGacha($usr->getUsrUserId(), $oprGacha->getId());

        $now = $request->getNow();
        $this->gachaService->validateExpiration($usrGacha, $now);

        // ステップアップガチャ固有の初期化とバリデーション
        $stepUpState = $this->stepUpGachaService->initializeAndValidate(
            $usrGacha,
            $oprGacha->getId(),
            $playNum,
            $currentStepNumber
        );
        $currentStepNumber = $stepUpState->getCurrentStepNumber();
        $loopCount = $stepUpState->getLoopCount();
        $fixedPrizeCount = $stepUpState->getStepUpGachaStep()->getFixedPrizeCount();

        // ステップアップガチャは天井なし
        $oprGachaUppers = collect();
        $usrGachaUppers = collect();

        // ガチャ抽選BOX取得
        $gachaLotteryBoxData = $this->stepUpGachaService->getLotteryBox(
            $oprGacha,
            $stepUpState->getStepUpGachaStep()
        );

        // 既に引いている数にズレがないかチェック
        $this->gachaService->validateDrewCount($drewCount, $usrGacha->getCount());

        // コスト検証
        $this->stepUpGachaService->validateCost(
            $stepUpState->getStepUpGachaStep(),
            $costType,
            $costId,
            $costNum,
            $loopCount
        );

        // ガシャ回数カウントとチェック
        $usrGacha->incrementPlayCount($playNum);
        $this->gachaService->validatePlayCount($oprGacha, $usrGacha);
        $usrGacha->setPlayedAt($now->toDateTimeString());

        // リソース消費に追加
        $this->gachaService->setConsumeResource(
            $oprGacha,
            $usrGacha,
            $usr->getUsrUserId(),
            $playNum,
            $costId,
            $costNum,
            $platform,
            $billingPlatform,
            false, // ステップアップガチャは広告なし
            $costType
        );

        // ガチャ抽選処理実行
        $gachaResultData = $this->gachaService->executeLottery(
            $oprGacha,
            $gachaLotteryBoxData,
            $playNum,
            $oprGachaUppers,
            $usrGachaUppers,
            false,
            $fixedPrizeCount
        );
        $gachaRewards = $this->gachaService->makeGachaRewardByGachaBoxes(
            $gachaResultData->getResult(),
            $oprGacha->getId()
        );
        $this->rewardDelegator->addRewards($gachaRewards);

        // おまけ報酬を取得して配布（ステップ進行前に実行）
        $this->stepUpGachaService->addStepRewards(
            $oprGacha->getId(),
            $currentStepNumber,
            $loopCount
        );

        // ステップアップガチャの場合はステップ進行
        $this->stepUpGachaService->progressStep($usrGacha, $stepUpState->getStepUpGacha());

        // ミッショントリガー送信
        $this->gachaMissionTriggerService->sendDrawTrigger($oprGacha->getId(), $playNum);

        // ログ
        $upperCounts = $usrGachaUppers->mapWithKeys(function (UsrGachaUpperInterface $upper) {
            return [$upper->getUpperType() => $upper->getCount()];
        });
        $logGachaAction = $this->logGachaActionRepository->create(
            $usr->getUsrUserId(),
            $oprGacha->getId(),
            $costType->value,
            $playNum,
            $upperCounts->get(UpperType::MAX_RARITY->value, 0),
            $upperCounts->get(UpperType::PICKUP->value, 0),
        );

        // ステップアップガチャの場合はログにステップ情報を追加
        $logGachaAction->setStepNumber($currentStepNumber);
        $logGachaAction->setLoopCount($loopCount);

        // ユーザデータの保存
        $this->gachaService->saveUsr($usrGacha, $usrGachaUppers);

        // ログ送信
        $this->gachaLogService->sendGachaLog(
            $usr->getUsrUserId(),
            $oprGacha->getId(),
            $gachaResultData,
            $costType->value,
            $playNum
        );

        return new GachaDrawResult(
            $gachaRewards,
            $usrGacha,
            $usrGachaUppers,
            $logGachaAction,
            $currentStepNumber,
            $loopCount
        );
    }

    /**
     * ステップアップガチャのリソース消費を実行
     *
     * @param ILogGachaAction $logGachaAction
     * @return void
     * @throws \Throwable
     */
    public function execConsumeResource(ILogGachaAction $logGachaAction): void
    {
        $this->gachaService->execConsumeResource($logGachaAction);
    }
}
