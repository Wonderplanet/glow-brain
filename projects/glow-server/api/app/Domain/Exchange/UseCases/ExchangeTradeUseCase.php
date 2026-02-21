<?php

declare(strict_types=1);

namespace App\Domain\Exchange\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Exchange\Repositories\UsrExchangeLineupRepository;
use App\Domain\Exchange\Services\ExchangeService;
use App\Domain\Resource\Entities\Rewards\ExchangeTradeReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\ExchangeTradeResultData;

class ExchangeTradeUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private ExchangeService $exchangeService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator,
        private UsrExchangeLineupRepository $usrExchangeLineupRepository,
    ) {
    }

    /**
     * 交換実行
     *
     * @param CurrentUser $user
     * @param string $mstExchangeId
     * @param string $mstExchangeLineupId
     * @param int $tradeCount
     * @param int $platform
     * @return ExchangeTradeResultData
     */
    public function exec(
        CurrentUser $user,
        string $mstExchangeId,
        string $mstExchangeLineupId,
        int $tradeCount,
        int $platform,
    ): ExchangeTradeResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $mstExchangeId,
            $mstExchangeLineupId,
            $tradeCount,
            $now,
            $platform,
        ) {
            $this->exchangeService->trade(
                $usrUserId,
                $mstExchangeId,
                $mstExchangeLineupId,
                $tradeCount,
                $now,
                $platform,
            );
        });

        // APIレスポンス用データ取得
        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $usrExchangeLineups = $this->usrExchangeLineupRepository->getChangedModels();

        return new ExchangeTradeResultData(
            $this->makeUsrParameterData($usrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrArtworks(),
            $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
            $usrExchangeLineups,
            $this->rewardDelegator->getSentRewards(ExchangeTradeReward::class),
        );
    }
}
