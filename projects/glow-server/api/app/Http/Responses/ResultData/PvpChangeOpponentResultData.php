<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\OpponentSelectStatusResponseData;
use Illuminate\Support\Collection;

class PvpChangeOpponentResultData
{
    /**
     * @param Collection<OpponentSelectStatusResponseData> $opponentSelectStatusResponses 対戦相手選択ステータスのレスポンスデータ
     */
    public function __construct(
        public Collection $opponentSelectStatusResponses,
    ) {
    }
}
