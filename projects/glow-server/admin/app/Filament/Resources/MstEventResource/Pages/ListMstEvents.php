<?php

namespace App\Filament\Resources\MstEventResource\Pages;

use App\Filament\Resources\MstEventResource;
use Filament\Resources\Pages\ListRecords;

class ListMstEvents extends ListRecords
{
    protected static string $resource = MstEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
