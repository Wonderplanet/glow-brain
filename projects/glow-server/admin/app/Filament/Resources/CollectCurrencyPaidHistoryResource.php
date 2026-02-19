<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\CollectCurrencyPaidHistoryResource\Pages;
use App\Filament\Tables\Columns\DateTimeColumn;
use App\Infolists\Components\DateTimeEntry;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Usr\UsrStoreProductHistory;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Fieldset;
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
use WonderPlanet\Domain\Currency\Entities\Trigger;

class CollectCurrencyPaidHistoryResource extends Resource
{
    use Authorizable;

    protected static ?string $model = LogCurrencyPaid::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 112;
    protected static ?string $navigationGroup = NavigationGroups::CS->value;

    protected static ?string $modelLabel = '有償一次通貨回収ログ';

    /**
     * 一覧表示の生成
     *
     * @param Table $table
     * @return Table
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
                ->columns([
                    TextColumn::make('usr_user_id')
                        ->label('ユーザーID')
                        ->sortable(),
                    TextColumn::make('trigger_id')
                        ->label('回収したユーザー購入履歴ID'),
                    TextColumn::make('currency_code')
                        ->label('通貨コード'),
                    TextColumn::make('purchase_price')
                        ->label('回収商品の金額'),
                    TextColumn::make('purchase_amount_inverted')
                        ->default(fn (Model $record) => -1 * (int)$record->purchase_amount)
                        ->label('回収した個数'),
                    TextColumn::make('trigger_detail')
                        ->label('コメント'),
                    DateTimeColumn::make('created_at')
                        ->label('作成日')
                        ->sortable(),
                ])
            ->defaultSort('created_at', 'desc')
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
                        return $query->where('usr_user_id', (string)($data['usr_user_id']));
                    }),
            ])
            ->actions([
                ViewAction::make()
            ], position: ActionsPosition::BeforeColumns);
    }

    /**
     * 有償一次通貨回収のtrigger_typeのみを対象にする
     *
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->whereIN(
                'trigger_type',
                [
                    Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN,
                    Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH,
                ]
            );
    }

    /**
     * 詳細Viewの表示用
     *
     * @param Infolist $infolist
     * @return Infolist
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('id'),
                TextEntry::make('usr_user_id')
                    ->label('ユーザーID'),
                TextEntry::make('currency_code')
                    ->label('通貨コード'),
                TextEntry::make('purchase_price')
                    ->label('回収商品の金額'),
                TextEntry::make('purchase_amount_inverted')
                    ->default(fn (Model $record) => -1 * (int)$record->purchase_amount)
                    ->label('回収した有償通貨個数'),
                TextEntry::make('price_per_amount')
                    ->label('単価'),
                TextEntry::make('before_amount')
                    ->label('回収前の有償通貨所持数'),
                TextEntry::make('current_amount')
                    ->label('回収後の有償通貨所持数'),
                TextEntry::make('os_platform')
                    ->label('プラットフォーム'),
                TextEntry::make('billing_platform')
                    ->label('課金プラットフォーム'),
                Fieldset::make('product')
                    ->label('回収した商品の購入情報詳細')
                    ->schema(function (Model $record) {
                        // 回収ツールで登録したUsrStoreProductHistoryの情報を表示
                        $usrStoreProductHistory = UsrStoreProductHistory::query()
                            ->where('id', $record->trigger_id)
                            ->get()
                            ->first();
                        $sandboxStr = $usrStoreProductHistory->is_sandbox
                            ? 'はい'
                            : 'いいえ';

                        return [
                            TextEntry::make('historyId')
                                ->default($usrStoreProductHistory->id)
                                ->label('購入履歴id'),
                            TextEntry::make('receiptUniqueId')
                                ->default($usrStoreProductHistory->receipt_unique_id)
                                ->label('レシートユニークID'),
                            TextEntry::make('productSubId')
                                ->default($usrStoreProductHistory->product_sub_id)
                                ->label('商品ID(product_sub_id)'),
                            TextEntry::make('platformProductId')
                                ->default($usrStoreProductHistory->platform_product_id)
                                ->label('プラットホーム商品ID(platform_product_id)'),
                            TextEntry::make('mstStoreProductId')
                                ->default($usrStoreProductHistory->mst_store_product_id)
                                ->label('マスタ商品ID(mst_store_product_id)'),
                            TextEntry::make('paidAmount')
                                ->default($usrStoreProductHistory->paid_amount)
                                ->label('有償通貨個数'),
                            TextEntry::make('freeAmount')
                                ->default($usrStoreProductHistory->free_amount)
                                ->label('無償通貨個数'),
                            TextEntry::make('purchasePrice')
                                ->default($usrStoreProductHistory->purchase_price)
                                ->label('商品の購入金額'),
                            TextEntry::make('vipPoint')
                                ->default($usrStoreProductHistory->vip_point)
                                ->label('vipポイント'),
                            TextEntry::make('vipPoint')
                                ->default($sandboxStr)
                                ->label('サンドボックスデータか'),
                            ];
                    })
                    ->columns(4),
                Fieldset::make('trigger')
                    ->label('トリガー')
                    ->schema([
                        TextEntry::make('trigger_type')
                            ->label('トリガータイプ'),
                        TextEntry::make('trigger_id')
                            ->label('トリガーID'),
                        TextEntry::make('trigger_name')
                            ->label('トリガー名'),
                        TextEntry::make('trigger_detail')
                            ->label('トリガー詳細'),
                    ])
                    ->columns(4),
                TextEntry::make('trigger_detail')
                    ->label('回収ツールで入力したコメント')
                    ->columnSpanFull(),
                DateTimeEntry::make('created_at')
                    ->label('作成日'),
                DateTimeEntry::make('updated_at')
                    ->label('更新日'),
            ]);
    }

    /**
     * @return array|\Filament\Resources\Pages\PageRegistration[]
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectCurrencyPaidHistories::route('/'),
            'view' => Pages\ViewCollectCurrencyPaidHistory::route('/{record}'),
        ];
    }
}
