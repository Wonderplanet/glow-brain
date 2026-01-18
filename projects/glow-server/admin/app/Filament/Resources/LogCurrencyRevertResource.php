<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\LogCurrencyRevertResource\Pages;
use App\Models\Log\LogCurrencyRevertHistory;
use Filament\Resources\Resource;

class LogCurrencyRevertResource extends Resource
{
    use Authorizable;

    protected static ?string $model = LogCurrencyRevertHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = NavigationGroups::CS->value;
    protected static ?int $navigationSort = 102;

    protected static ?string $modelLabel = '一次通貨返却';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogCurrencyRevert::route('/'),
            'detail' => Pages\DetailLogCurrencyRevert::route('/detail'),
        ];
    }
}
