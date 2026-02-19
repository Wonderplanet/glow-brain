<?php

namespace App\Filament\Resources\UsrUserResource\Pages;

use App\Filament\Resources\UsrUserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsrUsers extends ListRecords
{
    protected static string $resource = UsrUserResource::class;
    protected static ?string $title = 'ユーザー';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
