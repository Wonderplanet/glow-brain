<?php

declare(strict_types=1);

namespace App\Livewire\CollectCurrencyPaid;

use App\Filament\Resources\CollectCurrencyPaidHistoryResource;
use App\Filament\Resources\CollectCurrencyPaidResource;
use App\Filament\Tables\Columns\DateTimeColumn;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Usr\UsrStoreProductHistory;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use WonderPlanet\Domain\Currency\Entities\Trigger;

/**
 * 有償一次通貨回収ツール - 検索したユーザーIDの購入履歴を表示する
 */
class CollectCurrencyPaidList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    /**
     * dispatchで受け取るlistener
     *
     * @var array
     */
    protected $listeners = [
        'searchUpdated' => 'onSearchUpdated',
    ];

    /**
     * ユーザーID検索条件
     *
     * @var string|null
     */
    public ?string $userId = '';

    /**
     * ページのレンダリング
     *
     * @return void
     */
    public function render()
    {
        return view('livewire.collect-currency-paid.collect-currency-paid-list');
    }

    /**
     * 検索条件を更新する
     *
     * @param string $userId
     * @return void
     */
    public function onSearchUpdated(string $userId): void
    {
        $this->userId = $userId;

        $table = $this->getTable();
        $this->table($table);
    }

    /**
     * テーブル表示情報を作成
     *
     * @param Table $table
     * @return Table
     */
    private function table(Table $table): Table
    {
        // ユーザーのショップ購入履歴からデータを取得
        $query = UsrStoreProductHistory::query()
            ->select(
                'id',
                'receipt_unique_id',
                'os_platform',
                'usr_user_id',
                'device_id',
                'age',
                'product_sub_id',
                'platform_product_id',
                'mst_store_product_id',
                'currency_code',
                'receipt_bundle_id',
                'paid_amount',
                'free_amount',
                'purchase_price',
                'price_per_amount',
                'vip_point',
                'is_sandbox',
                'billing_platform',
                'created_at',
                'updated_at',
            );

        // 対象ユーザーの回収可能な購入データを取得する
        //  回収処理で作成したデータ(paid_amountが0以下)は除外
        $query
            ->where('usr_user_id', $this->userId)
            ->where('paid_amount', '>', 0);

        // 回収済みの購入データ表示の為、LogCurrencyPaidのtrigger_id(UsrStoreProductHistory.id)を取得
        $collectedHistoryIds = LogCurrencyPaid::query()
            ->select('trigger_id')
            ->where('usr_user_id', $this->userId)
            ->whereIN('trigger_type', [Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN, Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH])
            ->get()
            ->pluck('trigger_id')
            ->toArray();

        // 表示用の情報
        return $table->query($query)
            ->recordClasses(function (Model $record) use ($collectedHistoryIds) {
                return in_array($record->id, $collectedHistoryIds)
                    ? 'bg-gray-700' : null;
            })
            ->columns([
                DateTimeColumn::make('created_at')
                    ->label('商品購入日時')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID')
                    ->alignCenter(),
                TextColumn::make('receipt_unique_id')
                    ->label('レシートユニークID')
                    ->alignCenter(),
                TextColumn::make('product_sub_id')
                    ->label('プロダクトサブID')
                    ->alignCenter(),
                TextColumn::make('platform_product_id')
                    ->label('PFプロダクトID')
                    ->alignCenter(),
                TextColumn::make('mst_store_product_id')
                    ->label('マスタープロダクトID')
                    ->alignCenter(),
                TextColumn::make('currency_code')
                    ->label('通貨コード')
                    ->alignCenter(),
                TextColumn::make('purchase_price')
                    ->label('購入価格')
                    ->alignCenter(),
                TextColumn::make('paid_amount')
                    ->label('有償一次通貨の付与量')
                    ->alignCenter(),
                TextColumn::make('free_amount')
                    ->label('無償一次通貨の付与量')
                    ->alignCenter(),
            ])
            ->actions([
                // レコードの先頭に回収画面リンク/回収済み画面リンクを生成する
                Action::make('detail')
                    ->label(function (Model $record) use ($collectedHistoryIds) {
                        return in_array($record->id, $collectedHistoryIds)
                            ? '回収済み'
                            : '回収';
                    })
                    ->icon(function () {
                        return 'heroicon-o-plus-circle';
                    })
                    ->url(function (Model $record) use ($collectedHistoryIds) {
                        if (in_array($record->id, $collectedHistoryIds)) {
                            // 回収済みレコードの表示は、logCurrencyPaidから回収ツールで作成したログのidを取得
                            $logCurrencyPaidId = LogCurrencyPaid::query()
                                ->where('trigger_id', $record->id)
                                ->get()
                                ->first();

                            // 回収ログ詳細画面へのリンク生成
                            return CollectCurrencyPaidHistoryResource::getUrl('view', [
                                $logCurrencyPaidId->id
                            ]);
                        }

                        // 回収ツール詳細画面のリンク生成
                        return CollectCurrencyPaidResource::getUrl('detail', [
                            'historyId' => $record->id,
                            'userId' => $this->userId,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc');
    }
}
