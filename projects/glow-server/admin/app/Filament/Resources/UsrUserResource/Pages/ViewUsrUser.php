<?php

namespace App\Filament\Resources\UsrUserResource\Pages;

use App\Filament\Resources\UsrUserResource;
use Filament\Resources\Pages\ViewRecord;

class ViewUsrUser extends ViewRecord
{
    protected static string $resource = UsrUserResource::class;
    // protected static ?string $title = 'ユーザー詳細';

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
}
