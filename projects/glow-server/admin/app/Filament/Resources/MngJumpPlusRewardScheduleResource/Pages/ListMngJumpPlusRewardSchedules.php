<?php

namespace App\Filament\Resources\MngJumpPlusRewardScheduleResource\Pages;

use App\Filament\Resources\MngJumpPlusRewardScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMngJumpPlusRewardSchedules extends ListRecords
{
    protected static string $resource = MngJumpPlusRewardScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
