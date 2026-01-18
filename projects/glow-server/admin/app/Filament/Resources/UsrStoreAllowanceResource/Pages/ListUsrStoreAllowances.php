<?php

namespace App\Filament\Resources\UsrStoreAllowanceResource\Pages;

use App\Filament\Resources\UsrStoreAllowanceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsrStoreAllowances extends ListRecords
{
    protected static string $resource = UsrStoreAllowanceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
