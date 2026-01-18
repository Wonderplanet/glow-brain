<?php

namespace App\Filament\Resources\PvpRankingResource\Pages;

use App\Filament\Resources\PvpRankingResource;
use Filament\Resources\Pages\ListRecords;

class ListPvpRankings extends ListRecords
{
    protected static string $resource = PvpRankingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
