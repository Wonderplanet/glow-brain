<?php

namespace App\Filament\Resources\AdmUserResource\Pages;

use App\Filament\Resources\AdmUserResource;
use Filament\Resources\Pages\EditRecord;

class EditAdmUser extends EditRecord
{
    protected static string $resource = AdmUserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
