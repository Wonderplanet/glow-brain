<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Models\Log\LogTradeShopItem;
use App\Tables\Columns\MstIdColumn;
use App\Constants\UserSearchTabs;
use Filament\Tables\Filters\Filter;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Tables\Columns\RewardInfoColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\RewardInfoGetTrait;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;

class UserLogTradeShopItem extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-trade-shop-item';
    public string $currentTab = UserSearchTabs::LOG_TRADE_SHOP_ITEM->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogTradeShopItem::query()
            ->with([
                'mst_shop_items'
            ])
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('nginx_request_id')
                    ->label('APIリクエストID')
                    ->searchable()
                    ->sortable(),
                MstIdColumn::make('mst_trade_shop_info')
                    ->label('ショップアイテム情報')
                    ->searchable()
                    ->getMstIdUsing(
                        function ($record) {
                            return $record->mst_shop_item_id;
                        }
                    )
                    ->getMstDataNameUsing(
                        function ($record) {
                            return $record->mst_shop_item_id ?? '未設定';
                        }
                    )
                    ->getMstDetailPageUrlUsing(function ($record) {
                        return MstShopItems::getUrl(['mstShopItemId' => $record->mst_shop_items->id]);
                    }),

                RewardInfoColumn::make('cost_info')
                    ->label('消費コスト')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getCostRewardsDto());
                        }
                    )
                    ->searchable(),
                TextColumn::make('trade_count')
                    ->label('交換回数')
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('reward_info')
                    ->label('報酬情報')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getReceivedRewardDtos());
                        }
                    )
                    ->searchable(),
                RewardInfoColumn::make('before_reward_info')
                    ->label('報酬情報(変換前)')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getBeforeReceivedRewardDtos());
                        }
                    )
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('交換日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        Filter::make('mst_shop_item_id')
                            ->form([
                                TextInput::make('mst_shop_item_id')
                                    ->label('ショップアイテムID')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['mst_shop_item_id'])) {
                                    return $query;
                                }
                                return $query->where('mst_shop_item_id', 'like', "{$data['mst_shop_item_id']}%");
                            }),
                        Filter::make('cost_type')
                            ->form([
                                TextInput::make('cost_type')
                                    ->label('コストタイプ')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['cost_type'])) {
                                    return $query;
                                }
                                return $query->where('cost_type', 'like', "{$data['cost_type']}%");
                            }),
                    ]
                ),
                FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_trade_shop_item')
            ]);
    }
}

