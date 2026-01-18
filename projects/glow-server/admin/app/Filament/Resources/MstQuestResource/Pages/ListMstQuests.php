<?php

namespace App\Filament\Resources\MstQuestResource\Pages;

use App\Filament\Resources\MstQuestResource;
use Filament\Resources\Pages\ListRecords;

class ListMstQuests extends ListRecords
{
    protected static string $resource = MstQuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
