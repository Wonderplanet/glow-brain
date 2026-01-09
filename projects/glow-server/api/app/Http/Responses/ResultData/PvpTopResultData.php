<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\OpponentSelectStatusResponseData;
use App\Http\Responses\Data\PvpHeldStatusData;
use App\Http\Responses\Data\PvpPreviousSeasonResultData;
use App\Http\Responses\Data\UsrPvpStatusData;
use Illuminate\Support\Collection;

class PvpTopResultData
{
    /** * PVPトップ画面の結果データ
     * @param PvpHeldStatusData $pvpHeldStatusData PVP開催状況
     * @param UsrPvpStatusData $usrPvpStatusData ユーザのPVPステータス
     * @param Collection<OpponentSelectStatusResponseData> $opponentSelectStatusResponses 対戦相手選択ステータスのレスポンスデータ
     * @param PvpPreviousSeasonResultData|null $pvpPreviousSeasonResultData 前シーズンの結果データ（存在しない場合はnull）
     * @param bool $isViewableRanking ランキングが表示可能かどうか
     */
    public function __construct(
        public PvpHeldStatusData $pvpHeldStatusData,
        public UsrPvpStatusData $usrPvpStatusData,
        public Collection $opponentSelectStatusResponses,
        public ?PvpPreviousSeasonResultData $pvpPreviousSeasonResultData,
        public bool $isViewableRanking,
    ) {
    }
}
