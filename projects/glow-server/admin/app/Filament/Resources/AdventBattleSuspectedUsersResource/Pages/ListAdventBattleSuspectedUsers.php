<?php

namespace App\Filament\Resources\AdventBattleSuspectedUsersResource\Pages;

use App\Filament\Resources\AdventBattleSuspectedUsersResource;
use App\Livewire\AdventBattleSuspectedUserTest;
use Filament\Resources\Pages\ListRecords;

class ListAdventBattleSuspectedUsers extends ListRecords
{
    protected static string $resource = AdventBattleSuspectedUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
