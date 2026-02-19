<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Pvp\Entities\PvpResultPoints;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrPvpStatusData;
use Illuminate\Support\Collection;

class PvpEndResultData
{
    /**
     * PVP終了時の結果データ
     *
     * @param UsrPvpStatusData $usrPvpStatus ユーザのPVPステータス
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param PvpResultPoints $pvpResultPoints
     * @param Collection<\App\Domain\Resource\Entities\Rewards\PvpTotalScoreReward> $pvpTotalScoreRewards
     */
    public function __construct(
        public UsrPvpStatusData $usrPvpStatus,
        public UsrParameterData $usrParameterData,
        public Collection $usrItems,
        public Collection $usrEmblems,
        public PvpResultPoints $pvpResultPoints,
        public Collection $pvpTotalScoreRewards,
    ) {
    }
}
