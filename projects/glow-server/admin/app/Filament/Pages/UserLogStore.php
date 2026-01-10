<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Constants\UserSearchTabs;
use App\Models\Log\LogStore;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Models\Opr\OprProduct;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\UserResourceLogCurrencyTrait;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use App\Constants\OsType;
use App\Tables\Columns\OprProductInfoColumn;

class UserLogStore extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use UserResourceLogCurrencyTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-store';
    public string $currentTab = UserSearchTabs::LOG_STORE->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogStore::query()
            ->where('usr_user_id', $this->userId);

        $ids = $query->pluck('mst_store_product_id')->toArray();
        $oprProducts = OprProduct::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('receipt_unique_id')
                    ->label('購入レシートID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('購入日時')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('billing_platform')
                    ->label('プラットフォーム')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('os_platform')
                    ->label('OS')
                    ->searchable()
                    ->sortable(),
                OprProductInfoColumn::make('opr_product_info')
                    ->label('商品情報')
                    ->searchable()
                    ->getStateUsing(function ($record) use ($oprProducts) {
                        return $oprProducts->get($record?->mst_store_product_id);
                    }),
                TextColumn::make('receipt_bundle_id')
                    ->label('ストアから送られてきた商品のバンドルID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('有償一次通貨の付与量')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('free_amount')
                    ->label('無償一次通貨の付与量')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->label('実際の購入価格')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_per_amount')
                    ->label('単価')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_sandbox')
                    ->label('サンドボックス')
                    ->getStateUsing(function ($record): string {
                        return $record->is_sandbox ? '◯' : '×';
                    }),
            ])
            ->filters([
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
                SelectFilter::make('os_platform')
                    ->options(OsType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('os_platform', $data);
                    })
                    ->label('OS'),
                ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_store')
            ]);
    }
}
