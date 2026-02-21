<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\CollectCurrencyPaidResource\Pages;
use App\Models\Usr\UsrStoreProductHistory;
use Filament\Resources\Resource;

class CollectCurrencyPaidResource extends Resource
{
    use Authorizable;

    protected static ?string $model = UsrStoreProductHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 111;
    protected static ?string $navigationGroup = NavigationGroups::CS->value;
    protected static ?string $modelLabel = '有償一次通貨回収';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectCurrencyPaid::route('/'),
            'detail' => Pages\DetailCollectCurrencyPaid::route('/detail'),
        ];
    }
}
