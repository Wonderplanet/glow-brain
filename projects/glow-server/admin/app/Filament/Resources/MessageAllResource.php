<?php

namespace App\Filament\Resources;

use App\Facades\Promotion;
use App\Filament\Authorizable;
use App\Filament\Resources\MessageAllResource\Pages;
use App\Filament\Traits\MessageResourceTrait;
use App\Models\Adm\AdmMessageDistributionInput;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class MessageAllResource extends Resource
{
    use Authorizable;
    use MessageResourceTrait;

    protected static ?string $model = AdmMessageDistributionInput::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = '運営/CS';

    protected static ?string $modelLabel = '全体メッセージ配布';

    public static function canCreate(): bool
    {
        return !Promotion::isPromotionDestinationEnvironment();
    }

    public static function table(Table $table): Table
    {
        return self::getTable($table, true);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'edit' => Pages\EditMessage::route('/{record}/edit'),
            'target-id-detail' => Pages\TargetIdDetail::route('/{record}/target-id-detail'),
        ];
    }
}
