<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrPvpStatusData;
use Illuminate\Support\Collection;

class PvpAbortResultData
{
    /**
     * PVP中止時の結果データ
     *
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     */
    public function __construct(
        public UsrPvpStatusData $usrPvpStatus,
        public Collection $usrItems,
    ) {
    }
}
