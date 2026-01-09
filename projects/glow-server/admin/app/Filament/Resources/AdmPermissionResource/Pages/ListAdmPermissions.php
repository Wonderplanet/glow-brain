<?php

namespace App\Filament\Resources\AdmPermissionResource\Pages;

use App\Filament\Resources\AdmPermissionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmPermissions extends ListRecords
{
    protected static string $resource = AdmPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
