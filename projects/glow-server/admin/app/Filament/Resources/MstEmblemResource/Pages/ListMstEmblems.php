<?php

namespace App\Filament\Resources\MstEmblemResource\Pages;

use App\Filament\Resources\MstEmblemResource;
use Filament\Resources\Pages\ListRecords;

class ListMstEmblems extends ListRecords
{
    protected static string $resource = MstEmblemResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
