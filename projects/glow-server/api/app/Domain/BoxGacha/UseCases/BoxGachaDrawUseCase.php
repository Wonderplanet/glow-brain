<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\UseCases;

use App\Domain\BoxGacha\Repositories\UsrBoxGachaRepository;
use App\Domain\BoxGacha\Services\BoxGachaCostService;
use App\Domain\BoxGacha\Services\BoxGachaService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Entities\Rewards\BoxGachaReward;
use App\Domain\Resource\Mst\Repositories\MstBoxGachaGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstBoxGachaPrizeRepository;
use App\Domain\Resource\Mst\Repositories\MstBoxGachaRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\BoxGachaDrawResultData;

class BoxGachaDrawUseCase
{
    use UseCaseTrait;

    public function __construct(
        private BoxGachaService $boxGachaService,
        private BoxGachaCostService $boxGachaCostService,
        private MstBoxGachaRepository $mstBoxGachaRepository,
        private MstBoxGachaGroupRepository $mstBoxGachaGroupRepository,
        private MstBoxGachaPrizeRepository $mstBoxGachaPrizeRepository,
        private UsrBoxGachaRepository $usrBoxGachaRepository,
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $usr
     * @param string $mstBoxGachaId
     * @param int $drawCount
     * @param int $currentBoxLevel
     * @param int $platform
     * @return BoxGachaDrawResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $usr,
        string $mstBoxGachaId,
        int $drawCount,
        int $currentBoxLevel,
        int $platform
    ): BoxGachaDrawResultData {
        $now = $this->clock->now();

        // マスターデータ取得
        $mstBoxGacha = $this->mstBoxGachaRepository->getById($mstBoxGachaId, true);

        // 期間チェック
        $this->boxGachaService->validateBoxGachaPeriod($mstBoxGacha, $now);

        // ユーザーデータ取得
        $usrBoxGacha = $this->usrBoxGachaRepository->getOrCreate($usr->getUsrUserId(), $mstBoxGachaId);

        // 箱レベルの整合性チェック
        $this->boxGachaService->validateCurrentBoxLevel($currentBoxLevel, $usrBoxGacha);

        // 現在のBOXグループを取得
        $mstBoxGachaGroup = $this->mstBoxGachaGroupRepository->getByMstBoxGachaIdAndBoxLevel(
            $mstBoxGacha->getId(),
            $usrBoxGacha->getCurrentBoxLevel(),
            true
        );

        // 賞品リストを取得
        $mstBoxGachaPrizes = $this->mstBoxGachaPrizeRepository->getByMstBoxGachaGroupId($mstBoxGachaGroup->getId());

        // 残り在庫を計算
        $remainingStock = $this->boxGachaService->calculateRemainingStock($mstBoxGachaPrizes, $usrBoxGacha);

        // 抽選回数バリデーション
        $this->boxGachaService->validateDrawCount($drawCount, $remainingStock);

        // コスト消費チェック
        $this->boxGachaCostService->validateCost($usr->getUsrUserId(), $mstBoxGacha, $drawCount);

        // 抽選可能な賞品リストを取得
        $availableMstBoxGachaPrizes = $this->boxGachaService->getAvailablePrizes($mstBoxGachaPrizes, $usrBoxGacha);

        // 抽選実行（永続化・報酬登録・ログ作成も含む）
        $this->boxGachaService->draw(
            $usr->getUsrUserId(),
            $availableMstBoxGachaPrizes,
            $usrBoxGacha,
            $drawCount,
            $mstBoxGachaId
        );

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use (
            $usr,
            $mstBoxGacha,
            $drawCount,
            $usrBoxGacha,
            $now,
            $platform,
        ) {
            // アイテム消費
            $this->boxGachaCostService->consumeCost(
                $usr->getUsrUserId(),
                $mstBoxGacha,
                $drawCount,
                $usrBoxGacha->getCurrentBoxLevel(),
            );

            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usr->getUsrUserId(), $platform, $now);
        });

        return new BoxGachaDrawResultData(
            $this->makeUsrParameterData(
                $this->userDelegator->getUsrUserParameterByUsrUserId($usr->getUsrUserId())
            ),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
            $this->usrModelDiffGetService->getChangedUsrArtworks(),
            $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
            $usrBoxGacha,
            $this->rewardDelegator->getSentRewards(BoxGachaReward::class),
        );
    }
}
