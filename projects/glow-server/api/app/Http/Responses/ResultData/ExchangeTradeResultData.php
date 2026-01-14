<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Emblem\Models\UsrEmblemInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Exchange\Models\UsrExchangeLineupInterface;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Resource\Entities\Rewards\ExchangeTradeReward;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

/**
 * 交換実行のレスポンスデータ
 */
class ExchangeTradeResultData
{
    /**
     * @param Collection<UsrItemInterface> $usrItems
     * @param Collection<UsrEmblemInterface> $usrEmblems
     * @param Collection<UsrUnitInterface> $usrUnits
     * @param Collection<UsrArtworkInterface> $usrArtworks
     * @param Collection<UsrArtworkFragmentInterface> $usrArtworkFragments
     * @param Collection<UsrExchangeLineupInterface> $usrExchangeLineups
     * @param Collection<ExchangeTradeReward> $exchangeTradeRewards
     */
    public function __construct(
        public readonly UsrParameterData $usrUserParameter,
        public readonly Collection $usrItems,
        public readonly Collection $usrEmblems,
        public readonly Collection $usrUnits,
        public readonly Collection $usrArtworks,
        public readonly Collection $usrArtworkFragments,
        public readonly Collection $usrExchangeLineups,
        public readonly Collection $exchangeTradeRewards,
    ) {
    }
}
