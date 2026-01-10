<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use Filament\Pages\Page;

/**
 * 有償・無償一次通貨の購入・消費履歴を表示するページ
 */
class LogCurrencyHistory extends Page
{
    use Authorizable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.log-currency-history';
    protected static ?string $navigationGroup = NavigationGroups::CS->value;
    protected static ?string $title = '課金照会';
}
