<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services\Draw;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Enums\ContentType;
use App\Domain\Common\Services\AdPlayService;
use App\Domain\Gacha\Entities\GachaDrawRequest;
use App\Domain\Gacha\Entities\GachaDrawResult;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\Gacha\Repositories\LogGachaActionRepository;
use App\Domain\Gacha\Services\GachaLogService;
use App\Domain\Gacha\Services\GachaMissionTriggerService;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Resource\Mst\Repositories\OprGachaUpperRepository;
use App\Domain\Reward\Delegators\RewardDelegator;

/**
 * 標準ガチャ抽選サービス（ステップアップガチャ以外の全ガチャタイプに対応）
 */
class StandardGachaDrawService extends GachaDrawService
{
    public function __construct(
        GachaService $gachaService,
        GachaMissionTriggerService $gachaMissionTriggerService,
        GachaLogService $gachaLogService,
        RewardDelegator $rewardDelegator,
        Clock $clock,
        LogGachaActionRepository $logGachaActionRepository,
        private AdPlayService $adPlayService,
        private OprGachaUpperRepository $oprGachaUpperRepository,
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
     * 標準ガチャの抽選を実行する
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

        // ガシャ情報取得
        $usrGacha = $this->gachaService->getUsrGacha($usr->getUsrUserId(), $oprGacha->getId());

        $now = $request->getNow();
        $this->gachaService->validateExpiration($usrGacha, $now);

        // ガチャ天井情報取得
        $oprGachaUppers = collect();
        $usrGachaUppers = collect();
        if ($oprGacha->hasUpper()) {
            $oprGachaUppers = $this->oprGachaUpperRepository->getByUpperGroup($oprGacha->getUpperGroup());
            $usrGachaUppers = $this->gachaService->getUsrGachaUppers(
                $usr->getUsrUserId(),
                $oprGacha->getUpperGroup(),
                $oprGachaUppers->map(fn($upper) => $upper->getUpperType()->value),
            );
        }

        // ガチャ抽選BOX取得
        $gachaLotteryBoxData = $this->gachaService->getGachaLotteryBox($oprGacha);

        // 既に引いている数にズレがないかチェック
        $this->gachaService->validateDrewCount($drewCount, $usrGacha->getCount());

        // N連数のチェック
        $this->gachaService->validatePlayNum($playNum, $oprGacha->getMultiDrawCount());

        // コスト検証
        $this->gachaService->validateCostType($oprGacha, $costType);

        // コストの妥当性検証（通常ガチャ用）
        $this->gachaService->validateCost(
            $oprGacha,
            $playNum,
            $costId,
            $costNum,
            $costType
        );

        // ガシャ回数カウントとチェック
        $usrGacha->incrementPlayCount($playNum);
        $this->gachaService->validatePlayCount($oprGacha, $usrGacha);
        $usrGacha->setPlayedAt($now->toDateTimeString());

        $checkedAd = false;
        if ($costType === CostType::AD) {
            // 広告で引く場合
            $usrGacha->incrementAdPlayCount($playNum);
            $this->gachaService->validateAd($oprGacha, $usrGacha);
            $usrGacha->setAdPlayedAt($now->toDateTimeString());
            $checkedAd = true;

            // 広告視聴ログ・ミッショントリガー送信
            $this->adPlayService->adPlay(
                $usr->getUsrUserId(),
                ContentType::GACHA->value,
                $oprGacha->getId(),
                $now
            );
        }

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
            $checkedAd,
            $costType
        );

        // ガチャ抽選処理実行
        $gachaResultData = $this->gachaService->executeLottery(
            $oprGacha,
            $gachaLotteryBoxData,
            $playNum,
            $oprGachaUppers,
            $usrGachaUppers,
            $checkedAd,
            null
        );
        $gachaRewards = $this->gachaService->makeGachaRewardByGachaBoxes(
            $gachaResultData->getResult(),
            $oprGacha->getId(),
            $gachaResultData->getPrizeTypes()
        );
        $this->rewardDelegator->addRewards($gachaRewards);

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
            null,
            null
        );
    }
}
