<?php

namespace App\Filament\Resources\AdmPromotionTagResource\Pages;

use App\Filament\Resources\AdmPromotionTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdmPromotionTag extends EditRecord
{
    protected static string $resource = AdmPromotionTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
