<?php

namespace App\Filament\Resources\AdmPermissionResource\Pages;

use App\Filament\Resources\AdmPermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmPermission extends CreateRecord
{
    protected static string $resource = AdmPermissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
