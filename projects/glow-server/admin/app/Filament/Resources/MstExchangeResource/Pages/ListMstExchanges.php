<?php

namespace App\Filament\Resources\MstExchangeResource\Pages;

use App\Filament\Resources\MstExchangeResource;
use Filament\Resources\Pages\ListRecords;

class ListMstExchanges extends ListRecords
{
    protected static string $resource = MstExchangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
