<?php

namespace App\Tables\Columns;

use App\Filament\Pages\MstMissionAchievementDetail;
use App\Filament\Pages\MstMissionDailyDetail;
use App\Filament\Pages\MstMissionEventDailyDetail;
use App\Filament\Pages\MstMissionEventsDetail;
use App\Filament\Pages\MstMissionWeeklyDetail;
use App\Filament\Pages\MstMissionLimitedTermsDetail;
use App\Models\Mst\MstMissionAchievement;
use App\Models\Mst\MstMissionDaily;
use App\Models\Mst\MstMissionEvent;
use App\Models\Mst\MstMissionEventDaily;
use App\Models\Mst\MstMissionWeekly;
use App\Models\Mst\MstMissionLimitedTerm;
use Filament\Tables\Columns\Column;

class MstMissionInfoColumn extends Column
{
    protected string $view = 'tables.columns.mst-mission-info-column';

    public function link(string $id)
    {
        if ($this->record->relationLoaded('mst_mission_i18n')) {
            if ($this->record instanceof MstMissionAchievement)
            {
                return MstMissionAchievementDetail::getUrl(['mstMissionAchievementId' => $id]);
            }
            else if ($this->record instanceof MstMissionDaily)
            {
                return MstMissionDailyDetail::getUrl(['mstMissionDailyId' => $id]);
            }
            else if ($this->record instanceof MstMissionWeekly)
            {
                return MstMissionWeeklyDetail::getUrl(['mstMissionWeeklyId' => $id]);
            }
            else if ($this->record instanceof MstMissionEvent)
            {
                return MstMissionEventsDetail::getUrl(['mstMissionEventId' => $id]);
            }
            else if ($this->record instanceof MstMissionEventDaily)
            {
                return MstMissionEventDailyDetail::getUrl(['mstMissionEventDailyId' => $id]);
            }
            else if ($this->record instanceof MstMissionLimitedTerm)
            {
                return MstMissionLimitedTermsDetail::getUrl(['mstMissionLimitedTermId' => $id]);
            }
        }
    }
}
