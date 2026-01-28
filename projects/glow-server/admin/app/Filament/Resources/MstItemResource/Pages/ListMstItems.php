<?php

namespace App\Filament\Resources\MstItemResource\Pages;

use App\Filament\Resources\MstItemResource;
use Filament\Resources\Pages\ListRecords;

class ListMstItems extends ListRecords
{
    protected static string $resource = MstItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
