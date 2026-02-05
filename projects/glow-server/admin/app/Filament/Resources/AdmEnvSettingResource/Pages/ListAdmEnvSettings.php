<?php

namespace App\Filament\Resources\AdmEnvSettingResource\Pages;

use App\Filament\Resources\AdmEnvSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmEnvSettings extends ListRecords
{
    protected static string $resource = AdmEnvSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('新規作成'),
        ];
    }
}
