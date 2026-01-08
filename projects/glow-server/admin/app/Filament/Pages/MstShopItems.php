<?php

namespace App\Filament\Pages;

use App\Constants\ShopItemCostType;
use App\Constants\ShopItemResourceType;
use App\Constants\ShopTabs;
use App\Constants\ShopType;
use App\Filament\Pages\Shop\ShopDataBasePage;
use App\Models\Mst\MstShopItem;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\PageTrait;
use App\Traits\RewardInfoGetTrait;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class MstShopItems extends ShopDataBasePage implements HasTable
{
    use InteractsWithTable;
    use RewardInfoGetTrait;
    use PageTrait;

    protected static string $view = 'filament.pages.mst-shop-items';
    public string $currentTab = ShopTabs::SHOP->value;
    protected static ?string $title = ShopTabs::SHOP->value;

    public function getTableRecords(): Paginator | CursorPaginator
    {
        return $this->augmentPaginatorWithCallback(
            function (Paginator | CursorPaginator $paginator) {
                $this->addRewardInfoToPaginatedRecords(
                    $paginator,
                );
                $this->addRewardInfoToPaginatedRecords(
                    $paginator,
                    'cost',
                    'cost_info'
                );
            }
        );
    }

    public static function table(Table $table): Table
    {
        $query = MstShopItem::query();

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shop_type_label') // MstShopItemモデルのgetShopTypeLabelAttributeが呼ばれる
                    ->label('ショップタイプ')
                    ->tooltip(fn (MstShopItem $mstShopItem) => $mstShopItem->shop_type)
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('cost_info')
                    ->label('コスト情報'),
                TextColumn::make('is_first_time_free')
                    ->label('初回無料フラグ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tradable_count')
                    ->label('交換可能回数')
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('reward_info')
                    ->label('ショップアイテム情報'),
                TextColumn::make('start_date')
                    ->label('開始日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('終了日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('release_key')
                    ->label('リリースキー')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('shop_type')
                    ->options(ShopType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('shop_type', $data);
                    })
                    ->label('ショップタイプ'),
                SelectFilter::make('cost_type')
                    ->options(ShopItemCostType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('cost_type', $data);
                    })
                    ->label('コストタイプ'),
                Filter::make('is_first_time_free')
                    ->form([
                        TextInput::make('is_first_time_free')
                            ->label('初回無料フラグ')
                    ])
                    ->label('初回無料フラグ')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['is_first_time_free'] != 0 && blank($data['is_first_time_free'])) {
                            return $query;
                        }
                        return $query->where('is_first_time_free', (int)$data['is_first_time_free']);
                    }),
                SelectFilter::make('resource_type')
                    ->options(ShopItemResourceType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('resource_type', $data);
                    })
                    ->label('リソースタイプ'),
                SelectFilter::make('duration')
                    ->form([
                        DatePicker::make('datetime')
                                ->label('有効日時'),
                    ])
                    ->label('有効日時')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['datetime'])) {
                            return $query;
                        }
                        return $query->where('start_date', '<=', $data['datetime'])
                            ->where('end_date', '>=', $data['datetime']);
                    }),
                ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            );
    }
}
