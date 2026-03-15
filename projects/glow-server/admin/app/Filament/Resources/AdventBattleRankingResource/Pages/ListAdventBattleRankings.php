<?php

namespace App\Filament\Resources\AdventBattleRankingResource\Pages;

use App\Filament\Resources\AdventBattleRankingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdventBattleRankings extends ListRecords
{
    protected static string $resource = AdventBattleRankingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
