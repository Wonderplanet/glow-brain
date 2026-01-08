<?php

namespace App\Filament\Resources\UnauthorizedUserResource\Pages;

use App\Filament\Resources\SuspectedUsersResource;
use Filament\Resources\Pages\ListRecords;

class ListSuspectedUsers extends ListRecords
{
    protected static string $resource = SuspectedUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
