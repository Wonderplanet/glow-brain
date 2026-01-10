<?php

namespace App\Filament\Resources\MngContentCloseResource\Pages;

use App\Filament\Resources\MngContentCloseResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMngContentCloses extends ListRecords
{
    protected static string $resource = MngContentCloseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('新規作成'),
        ];
    }
}
