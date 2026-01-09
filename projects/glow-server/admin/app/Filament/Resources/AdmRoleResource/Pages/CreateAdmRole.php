<?php

namespace App\Filament\Resources\AdmRoleResource\Pages;

use App\Filament\Resources\AdmRoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmRole extends CreateRecord
{
    protected static string $resource = AdmRoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
