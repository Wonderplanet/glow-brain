<?php

namespace App\Filament\Resources\AdmInformationResource\Pages;

use App\Filament\Resources\AdmInformationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmInformation extends ListRecords
{
    protected static string $resource = AdmInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
