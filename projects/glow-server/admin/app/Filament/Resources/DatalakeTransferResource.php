<?php

namespace App\Filament\Resources;

use App\Constants\DatalakeStatus;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\DatalakeTransferResource\Pages;
use App\Models\Adm\AdmDatalakeLog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DatalakeTransferResource extends Resource
{
    use Authorizable;

    protected static ?string $model = AdmDatalakeLog::class;
    protected static ?string $modelLabel = 'データレイク転送管理';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::OTHER->value;
    protected static ?string $navigationLabel = 'データレイク転送管理';
    protected static ?int $navigationSort = 900;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('転送対象日'),
                TextColumn::make('status')
                    ->label('転送進捗')
                    ->formatStateUsing(function (AdmDatalakeLog $record): string {
                        $status = DatalakeStatus::tryFrom($record->getStatus());
                        return $status ? $status->label() : '不明';
                    })
                    ->sortable(),
                TextColumn::make('is_transfer')
                    ->label('転送状況')
                    ->formatStateUsing(function (AdmDatalakeLog $record) {
                        return $record->getIsTransfer() ? '転送中' : '停止中';
                    })
                    ->sortable(),
                TextColumn::make('try_count')
                    ->label('転送試行回数')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('実行日時')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('更新日時')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDatalakeTransfers::route('/'),
        ];
    }
}
