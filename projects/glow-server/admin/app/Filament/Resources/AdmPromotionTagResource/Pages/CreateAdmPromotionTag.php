<?php

namespace App\Filament\Resources\AdmPromotionTagResource\Pages;

use App\Filament\Resources\AdmPromotionTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmPromotionTag extends CreateRecord
{
    protected static string $resource = AdmPromotionTagResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
