<?php

namespace App\Filament\Resources\OprProductResource\Pages;

use App\Filament\Resources\OprProductResource;
use Filament\Resources\Pages\ListRecords;

class ListOprProducts extends ListRecords
{
    protected static string $resource = OprProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
