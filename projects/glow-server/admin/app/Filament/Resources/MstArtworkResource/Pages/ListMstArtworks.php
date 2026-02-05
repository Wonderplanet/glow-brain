<?php

namespace App\Filament\Resources\MstArtworkResource\Pages;

use App\Filament\Resources\MstArtworkResource;
use Filament\Resources\Pages\ListRecords;

class ListMstArtworks extends ListRecords
{
    protected static string $resource = MstArtworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
