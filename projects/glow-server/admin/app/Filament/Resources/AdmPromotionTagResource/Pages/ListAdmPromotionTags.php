<?php

namespace App\Filament\Resources\AdmPromotionTagResource\Pages;

use App\Filament\Resources\AdmPromotionTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmPromotionTags extends ListRecords
{
    protected static string $resource = AdmPromotionTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
