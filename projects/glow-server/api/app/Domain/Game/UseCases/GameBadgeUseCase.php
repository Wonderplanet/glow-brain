<?php

declare(strict_types=1);

namespace App\Domain\Game\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Game\Services\GameService;
use App\Domain\Resource\Mst\Repositories\MngContentCloseRepository;
use App\Http\Responses\ResultData\GameBadgeResultData;
use Carbon\CarbonImmutable;

class GameBadgeUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private GameService $gameService,
        private MngContentCloseRepository $mngContentCloseRepository,
    ) {
    }

    public function exec(CurrentUser $user, string $language): GameBadgeResultData
    {
        $usrUserId = $user->getUsrUserId();
        $now = $this->clock->now();
        $gameStartAt = CarbonImmutable::parse($user->getGameStartAt());

        $badgeData = $this->gameService->fetchBadge($usrUserId, $now, $language, $gameStartAt);

        // コンテンツクローズ一覧を取得（is_valid=1のみ、時刻に関係なく全て）
        $mngContentCloses = $this->mngContentCloseRepository->findActiveList();

        $this->processWithoutUserTransactionChanges();
        return new GameBadgeResultData($badgeData, $mngContentCloses);
    }
}
