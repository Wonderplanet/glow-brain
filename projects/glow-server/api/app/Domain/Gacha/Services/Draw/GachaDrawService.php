<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services\Draw;

use App\Domain\Common\Entities\Clock;
use App\Domain\Gacha\Entities\GachaDrawRequest;
use App\Domain\Gacha\Entities\GachaDrawResult;
use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Gacha\Repositories\LogGachaActionRepository;
use App\Domain\Gacha\Services\GachaLogService;
use App\Domain\Gacha\Services\GachaMissionTriggerService;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Reward\Delegators\RewardDelegator;

/**
 * ガチャ抽選サービスの抽象クラス
 */
abstract class GachaDrawService
{
    public function __construct(
        protected GachaService $gachaService,
        protected GachaMissionTriggerService $gachaMissionTriggerService,
        protected GachaLogService $gachaLogService,
        protected RewardDelegator $rewardDelegator,
        protected Clock $clock,
        protected LogGachaActionRepository $logGachaActionRepository,
    ) {
    }

    /**
     * ガチャ抽選を実行する
     *
     * @param GachaDrawRequest $request
     * @return GachaDrawResult
     * @throws \Throwable
     */
    abstract public function draw(GachaDrawRequest $request): GachaDrawResult;

    /**
     * 自身のGachaServiceを通じてリソース消費を実行
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
