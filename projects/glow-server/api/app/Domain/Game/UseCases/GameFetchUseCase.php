<?php

declare(strict_types=1);

namespace App\Domain\Game\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Game\Services\GameService;
use App\Http\Responses\ResultData\GameFetchResultData;
use Carbon\CarbonImmutable;

class GameFetchUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private GameService $gameService,
    ) {
    }

    public function exec(CurrentUser $user, string $language): GameFetchResultData
    {
        $usrUserId = $user->getUsrUserId();
        $now = $this->clock->now();
        $gameStartAt = CarbonImmutable::parse($user->getGameStartAt());

        $gameFetchData = $this->gameService->fetch($usrUserId, $now, $language, $gameStartAt);

        $this->processWithoutUserTransactionChanges();
        return new GameFetchResultData($gameFetchData);
    }
}
