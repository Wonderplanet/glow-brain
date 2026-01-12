<?php

namespace App\Filament\Pages\User;

use App\Filament\Authorizable;

class UserDetail extends UserDataBasePage
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.usr-user-detail';

    // メニューに出さない
    protected static bool $shouldRegisterNavigation = false;
}
