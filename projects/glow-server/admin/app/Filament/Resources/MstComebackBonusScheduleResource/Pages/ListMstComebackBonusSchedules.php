<?php

namespace App\Filament\Resources\MstComebackBonusScheduleResource\Pages;

use App\Filament\Resources\MstComebackBonusScheduleResource;
use Filament\Resources\Pages\ListRecords;

class ListMstComebackBonusSchedules extends ListRecords
{
    protected static string $resource = MstComebackBonusScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
