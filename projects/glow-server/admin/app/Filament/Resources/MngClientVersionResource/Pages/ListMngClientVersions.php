<?php

namespace App\Filament\Resources\MngClientVersionResource\Pages;

use App\Filament\Resources\MngClientVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMngClientVersions extends ListRecords
{
    protected static string $resource = MngClientVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
