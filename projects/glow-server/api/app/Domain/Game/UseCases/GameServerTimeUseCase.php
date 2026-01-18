<?php

declare(strict_types=1);

namespace App\Domain\Game\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Http\Responses\ResultData\GameServerTimeResultData;
use Carbon\CarbonImmutable;

class GameServerTimeUseCase
{
    use UseCaseTrait;

    public function __construct()
    {
    }

    public function __invoke(CurrentUser $user): GameServerTimeResultData
    {
        $this->processWithoutUserTransactionChanges();
        return new GameServerTimeResultData(CarbonImmutable::now());
    }
}
