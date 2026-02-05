<?php

namespace App\Filament\Resources\AdmRoleResource\Pages;

use App\Filament\Resources\AdmRoleResource;
use Filament\Resources\Pages\EditRecord;

class EditAdmRole extends EditRecord
{
    protected static string $resource = AdmRoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
