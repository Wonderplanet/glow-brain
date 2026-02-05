<?php

namespace App\Filament\Resources\IgnSettingResource\Pages;

use App\Filament\Resources\IgnSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIgnSettings extends ListRecords
{
    protected static string $resource = IgnSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('新規作成'),
        ];
    }
}
