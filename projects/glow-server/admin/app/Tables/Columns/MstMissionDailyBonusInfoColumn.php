<?php

namespace App\Tables\Columns;

use App\Filament\Pages\MstMissionDailyBonusDetail;
use App\Filament\Pages\MstMissionEventDailyBonusDetail;
use App\Models\Mst\MstMissionDailyBonus;
use App\Models\Mst\MstMissionEventDailyBonus;
use Filament\Tables\Columns\Column;

class MstMissionDailyBonusInfoColumn extends Column
{
    protected string $view = 'tables.columns.mst-mission-daily-bonus-info-column';

    public function link(string $id)
    {
        if ($this->record instanceof MstMissionDailyBonus)
        {
            return MstMissionDailyBonusDetail::getUrl(['mstMissionDailyBonusDailyId' => $id]);
        }
        else if ($this->record instanceof MstMissionEventDailyBonus)
        {
            return MstMissionEventDailyBonusDetail::getUrl(['mstMissionEventDailyBonusDailyId' => $id]);
        }
    }
}
