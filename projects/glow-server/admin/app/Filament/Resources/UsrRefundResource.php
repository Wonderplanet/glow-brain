<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Constants\OsType;
use App\Constants\SystemConstants;
use App\Domain\AppStore\Models\LogAppStoreRefund;
use App\Domain\GooglePlay\Models\LogGooglePlayRefund;
use App\Filament\Authorizable;
use App\Filament\Pages\UserRefundDetail;
use App\Filament\Resources\UsrRefundResource\Pages;
use App\Models\Usr\UsrStoreProductHistory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UsrRefundResource extends Resource
{
    use Authorizable;

    protected static ?string $model = UsrStoreProductHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::USER->value;
    protected static ?string $navigationLabel = '課金キャンセル履歴';

    protected static ?string $modelLabel = '課金キャンセル履歴';

    public static function table(Table $table): Table
    {
        $query = LogGooglePlayRefund::query()
            ->select([
                'log_google_play_refunds.refunded_at as refunded_at',
                'usr_store_product_histories.id as id',
                'usr_store_product_histories.os_platform as os_platform',
                'usr_store_product_histories.usr_user_id as usr_user_id',
                'usr_store_product_histories.purchase_price as purchase_price',
                'usr_store_product_histories.product_sub_id as product_sub_id',
                'usr_store_product_histories.currency_code as currency_code',
                'usr_store_product_histories.is_sandbox as is_sandbox',
                'usr_store_product_histories.deleted_at as deleted_at',
            ])
            ->join('usr_store_product_histories', function ($join) {
                $join->on('log_google_play_refunds.transaction_id', '=', 'usr_store_product_histories.receipt_unique_id');
            })->unionAll(
                LogAppStoreRefund::query()
                    ->select([
                        'log_app_store_refunds.refunded_at as refunded_at',
                        'usr_store_product_histories.id as id',
                        'usr_store_product_histories.os_platform as os_platform',
                        'usr_store_product_histories.usr_user_id as usr_user_id',
                        'usr_store_product_histories.purchase_price as purchase_price',
                        'usr_store_product_histories.product_sub_id as product_sub_id',
                        'usr_store_product_histories.currency_code as currency_code',
                        'usr_store_product_histories.is_sandbox as is_sandbox',
                        'usr_store_product_histories.deleted_at as deleted_at',
                    ])
                    ->join('usr_store_product_histories', function ($join) {
                        $join->on('log_app_store_refunds.transaction_id', '=', 'usr_store_product_histories.receipt_unique_id');
                    })
            );
        $query = LogGooglePlayRefund::query()->fromSub($query, 'refunds')->select('*');

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID')
                    ->searchable(),
                TextColumn::make('os_platform')
                    ->label('OS')
                    ->searchable(),
                TextColumn::make('purchase_price')
                    ->label('購入価格'),
                TextColumn::make('product_sub_id')
                    ->label('プロダクトサブID'),
                TextColumn::make('currency_code')
                    ->label('通貨コード'),
                TextColumn::make('is_sandbox')
                    ->label('サンドボックス')
                    ->getStateUsing(function (Model $row): string {
                        return $row->is_sandbox ? '◯' : '×';
                    })->alignCenter(),
                TextColumn::make('refunded_at')
                    ->label('課金キャンセル日時')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s')
                    ->searchable()
                    ->sortable(),
            ])
            ->searchable(false)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
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
                SelectFilter::make('os_platform')
                    ->options(OsType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('os_platform', $data['value']);
                    })
                    ->label('OS'),
                Filter::make('refunded_at')
                    ->form([
                        DateTimePicker::make('refunded_from')->label('課金キャンセル日時開始'),
                        DateTimePicker::make('refunded_to')->label('課金キャンセル日時終了')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['refunded_from']) && blank($data['refunded_to'])) {
                            return $query;
                        }

                        return $query->whereBetween('refunded_at', [
                            $data['refunded_from'] ?? '1970-01-01 00:00:00',
                            $data['refunded_to'] ?? '9999-12-31 23:59:59'
                        ]);
                    }),
            ], FiltersLayout::AboveContent)
            ->defaultSort('refunded_at')
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return UserRefundDetail::getUrl([
                            'userId' => $record->usr_user_id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
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
            'index' => Pages\ListUsrRefunds::route('/'),
        ];
    }
}
