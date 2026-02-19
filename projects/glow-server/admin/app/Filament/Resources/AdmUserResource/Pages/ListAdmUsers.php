<?php

namespace App\Filament\Resources\AdmUserResource\Pages;

use App\Filament\Resources\AdmUserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmUsers extends ListRecords
{
    protected static string $resource = AdmUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
