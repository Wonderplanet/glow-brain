<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Constants\SystemConstants;
use App\Filament\Authorizable;
use App\Filament\Resources\UsrStoreProductHistoryResource\Pages;
use App\Models\Usr\UsrStoreProductHistory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UsrStoreProductHistoryResource extends Resource
{
    use Authorizable;

    protected static ?string $model = UsrStoreProductHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::USER->value;
    protected static ?string $navigationLabel = '課金履歴（プレイヤー別）';

    protected static ?string $modelLabel = '課金履歴（プレイヤー別）';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID'),
                TextColumn::make('os_platform')
                    ->label('OS'),
                TextColumn::make('billing_platform')
                    ->label('課金プラットフォーム'),
                TextColumn::make('product_sub_id')
                    ->label('プロダクトサブID'),
                TextColumn::make('currency_code')
                    ->label('通貨コード'),
                TextColumn::make('purchase_price')
                    ->label('購入価格'),
                TextColumn::make('paid_amount')
                    ->label('有償一次通貨付与数'),
                TextColumn::make('free_amount')
                    ->label('無償一次通貨付与数'),
                TextColumn::make('vip_point')
                    ->label('VIPポイント付与数'),
                TextColumn::make('is_sandbox')
                    ->label('サンドボックス')
                    ->getStateUsing(function (Model $row): string {
                        return $row->is_sandbox ? '◯' : '×';
                    })->alignCenter(),
                TextColumn::make('created_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('usr_user_id')
                    ->form([
                        TextInput::make('usr_user_id')
                            ->label('ユーザーID')
                    ])
                    ->label('ユーザーID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['usr_user_id'])) {
                            return $query;
                        }
                        return $query->where('usr_user_id', "{$data['usr_user_id']}");
                    }),
                Filter::make('created_at')
                    ->label('購入日時')
                    ->form([
                        DateTimePicker::make('created_from'),
                        DateTimePicker::make('created_to')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['created_from']) && blank($data['created_to'])) {
                            return $query;
                        }
                        // 入力をJSTからUTCに変換
                        if (!blank($data['created_from'])) {
                            $carbon = new Carbon($data['created_from'], SystemConstants::FORM_INPUT_TIMEZONE);
                            $data['created_from'] = $carbon->setTimezone(SystemConstants::DB_TIMEZONE)->toDateTimeString();
                        }
                        if (!blank($data['created_to'])) {
                            $carbon = new Carbon($data['created_to'], SystemConstants::FORM_INPUT_TIMEZONE);
                            $data['created_to'] = $carbon->setTimezone(SystemConstants::DB_TIMEZONE)->toDateTimeString();
                        }

                        $query->whereBetween('created_at', [
                            $data['created_from'] ?? '1970-01-01 00:00:00',
                            $data['created_to'] ?? '9999-12-31 23:59:59'
                        ]);
                        return $query;
                    }),
            ])->actions([
                ViewAction::make(),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('id'),
                TextEntry::make('usr_user_id')
                    ->label('ユーザーID'),
                TextEntry::make('os_platform')
                    ->label('OS'),
                TextEntry::make('billing_platform')
                    ->label('課金プラットフォーム'),
                TextEntry::make('receipt_unique_id')
                    ->label('レシートユニークID'),
                TextEntry::make('device_id')
                    ->label('デバイスID'),
                TextEntry::make('age')
                    ->label('年齢認証'),
                TextEntry::make('product_sub_id')
                    ->label('プロダクトサブID'),
                TextEntry::make('platform_product_id')
                    ->label('プロダクトID'),
                TextEntry::make('mst_store_product_id')
                    ->label('マスターストアプロダクトID'),
                TextEntry::make('currency_code')
                    ->label('通貨コード'),
                TextEntry::make('receipt_bundle_id')
                    ->label('レシートのバンドルID'),
                TextEntry::make('paid_amount')
                    ->label('有償一次通貨付与数'),
                TextEntry::make('free_amount')
                    ->label('無償一次通貨付与数'),
                TextEntry::make('purchase_price')
                    ->label('購入価格'),
                TextEntry::make('price_per_amount')
                    ->label('一次通貨単価'),
                TextEntry::make('vip_point')
                    ->label('VIPポイント付与数'),
                TextEntry::make('is_sandbox')
                    ->label('サンドボックス')
                    ->getStateUsing(function (Model $row): string {
                        return $row->is_sandbox ? '◯' : '×';
                    }),
                TextEntry::make('created_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s'),
                TextEntry::make('updated_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsrStoreProductHistories::route('/'),
            'view' => Pages\ViewUsrStoreProductHistory::route('/{record}'),
        ];
    }
}
