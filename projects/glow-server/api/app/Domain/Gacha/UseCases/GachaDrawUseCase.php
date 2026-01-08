<?php

declare(strict_types=1);

namespace App\Domain\Gacha\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Enums\ContentType;
use App\Domain\Common\Services\AdPlayService;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\Gacha\Repositories\LogGachaActionRepository;
use App\Domain\Gacha\Services\GachaLogService;
use App\Domain\Gacha\Services\GachaMissionTriggerService;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\GachaDrawResultData;

class GachaDrawUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Delegators
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
        // Services
        private GachaService $gachaService,
        private GachaMissionTriggerService $gachaMissionTriggerService,
        private GachaLogService $gachaLogService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private Clock $clock,
        private LogGachaActionRepository $logGachaActionRepository,
        private AdPlayService $adPlayService,
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
        CostType $costType
    ): GachaDrawResultData {
        // ガシャ情報取得
        $oprGacha = $this->gachaService->getOprGacha($oprGachaId);
        $usrGacha = $this->gachaService->getUsrGacha($usr->getUsrUserId(), $oprGacha->getId());

        $now = $this->clock->now();
        $this->gachaService->validateExpiration($usrGacha, $now);

        // ガシャ天井情報取得
        $oprGachaUppers = collect();
        $usrGachaUppers = collect();
        if ($oprGacha->hasUpper()) {
            $oprGachaUppers = $this->gachaService->getOprGachaUppers($oprGacha->getUpperGroup());
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

        // ガシャタイプに対するコスト種類が適切かチェック
        $this->gachaService->validateCostType($oprGacha, $costType);

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
            $usr->getUsrUserId(),
            $playNum,
            $costId,
            $costNum,
            $platform,
            $billingPlatform,
            $checkedAd,
            $costType
        );

        // ガシャ抽選
        $gachaResultData = $this->gachaService->draw(
            $oprGacha,
            $gachaLotteryBoxData,
            $playNum,
            $oprGachaUppers,
            $usrGachaUppers,
            $checkedAd,
        );
        $gachaRewards = $this->gachaService->makeGachaRewardByGachaBoxes($gachaResultData->getResult(), $oprGachaId);
        $this->rewardDelegator->addRewards($gachaRewards);

        // ミッショントリガー送信
        $this->gachaMissionTriggerService->sendDrawTrigger($oprGacha->getId(), $playNum);

        // ログ
        $upperCounts = $usrGachaUppers->mapWithKeys(function (UsrGachaUpperInterface $upper) {
            // $usrGachaUppers取得時にupper_groupを条件にしているため、upper_typeは重複しない
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
            $oprGachaId,
            $gachaResultData,
            $costType->value,
            $playNum
        );

        // トランザクションで囲んでDB更新処理を実行する
        $this->applyUserTransactionChanges(function () use (
            $usr,
            $now,
            $platform,
            $logGachaAction,
            $oprGachaId,
            $costType,
            $costId,
            $costNum,
            $playNum,
        ) {
            // リソース消費を実行
            $this->gachaService->execConsumeResource($logGachaAction);

            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usr->getUsrUserId(), $platform, $now);

            // ガチャ履歴のキャッシュ保存
            $gachaRewards = $this->rewardDelegator->getSentRewards(GachaReward::class);
            $this->gachaService->addGachaHistory(
                $usr->getUsrUserId(),
                $oprGachaId,
                $costType->value,
                $costId,
                $costNum,
                $playNum,
                $now,
                $gachaRewards
            );
        });

        // レスポンス用データ作成
        $gachaRewards = $this
            ->rewardDelegator
            ->getSentRewards(GachaReward::class)
            ->sortBy(fn(GachaReward $reward) => $reward->getSortOrder())
            ->values();
        return new GachaDrawResultData(
            $gachaRewards,
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->makeUsrParameterData(
                $this->userDelegator->getUsrUserParameterByUsrUserId($usr->getUsrUserId())
            ),
            $usrGacha,
            $usrGachaUppers
        );
    }
}
