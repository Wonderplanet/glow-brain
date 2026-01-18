<?php

namespace App\Filament\Resources\AdmUserResource\Pages;

use App\Filament\Resources\AdmUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmUser extends CreateRecord
{
    protected static string $resource = AdmUserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
