<?php

namespace App\Filament\Resources\OprCampaignResource\Pages;

use App\Filament\Resources\OprCampaignResource;
use Filament\Resources\Pages\ListRecords;

class ListOprCampaign extends ListRecords
{
    protected static string $resource = OprCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
