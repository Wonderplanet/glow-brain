<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Constants\UserSearchTabs;
use App\Models\Log\LogCurrencyPaid;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Models\Opr\OprProduct;
use App\Models\Usr\UsrStoreProductHistory;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\UserResourceLogCurrencyTrait;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use App\Tables\Columns\OprProductInfoColumn;

class UserLogCurrencyPaid extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use UserResourceLogCurrencyTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-currency-paid';
    public string $currentTab = UserSearchTabs::LOG_CURRENCY_PAID->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogCurrencyPaid::query()
            ->where('usr_user_id', $this->userId);

        $receiptUniqueIds = $query->pluck('receipt_unique_id')->toArray();
        $usrStoreProductHistorys = UsrStoreProductHistory::query()
            ->where('usr_user_id', $this->userId)
            ->whereIn('receipt_unique_id', $receiptUniqueIds)
            ->get()
            ->keyBy('receipt_unique_id');

        $ids = $usrStoreProductHistorys->pluck('product_sub_id')->toArray();
        $oprProducts = OprProduct::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $baseColumns = [
            TextColumn::make('receipt_unique_id')
                ->label('購入レシートID')
                ->searchable()
                ->sortable(),
            TextColumn::make('os_platform')
                ->label('OS')
                ->searchable()
                ->sortable(),
            // 有償購入前の残高
            TextColumn::make('before_amount')
                ->label('購入前の残高'),
            // 有償購入後の残高
            TextColumn::make('current_amount')
                ->label('購入後の残高')
                ->getStateUsing(function ($record): string {
                    // 現在の残高と増減分を表示
                    $result = $record->current_amount .
                        ' (' .
                        ($record->change_amount > 0 ? '+' : '') .
                        $record->change_amount .
                        ')';
                    return $result;
                }),
            TextColumn::make('purchase_price')
                ->label('支払い金額')
                ->getStateUsing(function ($record) use ($usrStoreProductHistorys) {
                    if ( array_key_exists($record->receipt_unique_id, $usrStoreProductHistorys->toArray()) ){
                        return $usrStoreProductHistorys[$record->receipt_unique_id]['purchase_price'];
                    }
                    return;
                }),
            OprProductInfoColumn::make('opr_product_info')
                ->label('商品情報')
                ->searchable()
                ->getStateUsing(function ($record) use ($usrStoreProductHistorys, $oprProducts) {
                    if ( array_key_exists($record->receipt_unique_id, $usrStoreProductHistorys->toArray()) ){
                        $productSubId = $usrStoreProductHistorys[$record->receipt_unique_id]['product_sub_id'];
                        return $oprProducts->get($productSubId);
                    }
                }),
            TextColumn::make('billing_platform')
                ->label('プラットフォーム')
                ->searchable()
                ->sortable(),
            TextColumn::make('is_sandbox')
                ->label('サンドボックス')
                ->getStateUsing(function ($record): string {
                    return $record->is_sandbox ? '◯' : '×';
                }),
        ];

        $addColumns = $this->getResourceLogCurrencyColumns();

        $columns = array_merge($baseColumns, $addColumns);

        $baseFilters = [
            Filter::make('receipt_unique_id')
                ->form([
                    TextInput::make('receipt_unique_id')
                        ->label('購入レシートID')
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['receipt_unique_id'])) {
                        return $query;
                    }
                    return $query->where('receipt_unique_id', 'like', "{$data['receipt_unique_id']}%");
                }),
            SelectFilter::make('billing_platform')
                ->options([
                    CurrencyConstants::PLATFORM_APPSTORE => CurrencyConstants::PLATFORM_APPSTORE,
                    CurrencyConstants::PLATFORM_GOOGLEPLAY => CurrencyConstants::PLATFORM_GOOGLEPLAY
                ])
                ->query(function (Builder $query, $data): Builder {
                    if (blank($data['value'])) {
                        return $query;
                    }
                    return $query->where('billing_platform', $data);
                })
                ->label('プラットフォーム'),
        ];

        $addFilters = $this->getResourceLogCurrencyFilters();
        $filters = array_merge($baseFilters, $addFilters);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns(
                $columns
            )
            ->filters(
                $filters
                , FiltersLayout::AboveContent)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_currency_paid')
            ]);
    }
}
