<?php

namespace App\Filament\Resources\ReleaseControlResource\Pages;

use App\Filament\Resources\ReleaseControlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReleaseControls extends ListRecords
{
    protected static string $resource = ReleaseControlResource::class;

    /**
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
