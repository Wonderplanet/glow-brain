<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use App\Filament\Resources\MessageIndividualResource\Pages;
use App\Filament\Traits\MessageResourceTrait;
use App\Models\Adm\AdmMessageDistributionIndividualInput;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class MessageIndividualResource extends Resource
{
    use Authorizable;
    use MessageResourceTrait;

    protected static ?string $model = AdmMessageDistributionIndividualInput::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = '運営/CS';

    protected static ?string $modelLabel = '個別メッセージ配布';

    public static function table(Table $table): Table
    {
        return self::getTable($table, false);
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
