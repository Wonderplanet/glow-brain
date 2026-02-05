<?php

declare(strict_types=1);

namespace App\Domain\Game\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Game\Services\GameService;
use App\Http\Responses\ResultData\GameUpdateAndFetchResultData;
use Carbon\CarbonImmutable;

class GameUpdateAndFetchUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private GameService $gameService,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $language,
        int $platform,
        string $accessToken,
        ?string $countryCode = null,
        ?string $adId = null
    ): GameUpdateAndFetchResultData {
        $usrUserId = $user->getUsrUserId();
        $now = $this->clock->now();
        $gameStartAt = CarbonImmutable::parse($user->getGameStartAt());

        // トランザクション処理
        $gameUpdateData = $this->applyUserTransactionChanges(
            function () use ($usrUserId, $platform, $now, $language, $gameStartAt, $countryCode, $adId) {
                return $this->gameService->update(
                    $usrUserId,
                    $platform,
                    $now,
                    $language,
                    $gameStartAt,
                    $countryCode,
                    $adId
                );
            }
        );

        // レスポンス用意
        $gameFetchData = $this->gameService->fetch($usrUserId, $now, $language, $gameStartAt);
        $gameFetchOtherData = $this->gameService->fetchOther($usrUserId, $now, $accessToken, $language);

        return new GameUpdateAndFetchResultData(
            $gameFetchData,
            $gameFetchOtherData,
            $gameUpdateData,
        );
    }
}
