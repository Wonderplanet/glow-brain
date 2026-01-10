<?php

namespace App\Filament\Resources\PvpSuspectedUsersResource\Pages;

use App\Filament\Resources\PvpSuspectedUsersResource;
use Filament\Resources\Pages\ListRecords;

class ListPvpSuspectedUsers extends ListRecords
{
    protected static string $resource = PvpSuspectedUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
