<?php

namespace App\Filament\Resources\AdmRoleResource\Pages;

use App\Filament\Resources\AdmRoleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmRoles extends ListRecords
{
    protected static string $resource = AdmRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
