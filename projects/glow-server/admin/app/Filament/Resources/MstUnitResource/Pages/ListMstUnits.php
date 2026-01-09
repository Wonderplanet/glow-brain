<?php

namespace App\Filament\Resources\MstUnitResource\Pages;

use App\Filament\Resources\MstUnitResource;
use Filament\Resources\Pages\ListRecords;

class ListMstUnits extends ListRecords
{
    protected static string $resource = MstUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
