<?php

namespace App\Filament\Resources\MstAdventBattleResource\Pages;

use App\Filament\Resources\MstAdventBattleResource;
use Filament\Resources\Pages\ListRecords;

class ListMstAdventBattles extends ListRecords
{
    protected static string $resource = MstAdventBattleResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
