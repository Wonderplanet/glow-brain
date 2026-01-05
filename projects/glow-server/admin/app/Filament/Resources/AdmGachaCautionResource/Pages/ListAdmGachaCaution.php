<?php

namespace App\Filament\Resources\AdmGachaCautionResource\Pages;

use App\Filament\Resources\AdmGachaCautionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmGachaCaution extends ListRecords
{
    protected static string $resource = AdmGachaCautionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
