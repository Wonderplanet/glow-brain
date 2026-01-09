<?php

namespace App\Filament\Pages;

use App\Constants\ShopType;
use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstShopItem;
use App\Services\Reward\RewardInfoGetHandleService;
use App\Tables\Columns\MstShopItemInfoColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserShopItem extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-shop-item';

    public string $currentTab = UserSearchTabs::SHOP_BASIC->value;

    private ?RewardInfoGetHandleService $rewardInfoGetHandleService = null;

    private Collection $rewardInfos;

    public function mount(): void
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => UserSearchTabs::SHOP_BASIC->value,
        ]);
    }

    public function initializeService(): void
    {
        if ($this->rewardInfoGetHandleService === null) {
            $this->rewardInfoGetHandleService = app(RewardInfoGetHandleService::class);
        }
    }

    public function table(Table $table): Table
    {
        $query = MstShopItem::query()
            ->with([
                'usr_shop_item' => function ($query) {
                    $query->where('usr_user_id', $this->userId);
                },
            ]);

        $rewardDtoList = $query->get()->map(function (MstShopItem $mstShopItem) {
            return $mstShopItem->reward;
        });
        $this->initializeService();
        $this->rewardInfos = $this->rewardInfoGetHandleService->build($rewardDtoList)->getRewardInfos();

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopItem $mstShopItem) {
                            return $mstShopItem->usr_shop_item?->id ?? 'レコードなし';
                        }
                    ),
                MstShopItemInfoColumn::make('reward_info')
                    ->label('ショップアイテム情報')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopItem $mstShopItem) {
                            return $this->rewardInfos->get($mstShopItem->id);
                        }
                    ),
                TextColumn::make('trade_count')
                    ->label('交換回数')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopItem $mstShopItem) {
                            return $mstShopItem->usr_shop_item?->trade_count ?? 0;
                        }
                    ),
                TextColumn::make('trade_total_count')
                    ->label('累計交換回数')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopItem $mstShopItem) {
                            return $mstShopItem->usr_shop_item?->trade_total_count ?? 0;
                        }
                    ),
                TextColumn::make('last_reset_at')
                    ->label('最終リセット日時')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopItem $mstShopItem) {
                            return $mstShopItem->usr_shop_item?->last_reset_at ?? "-";
                        }
                    ),
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
            )
            ->actions([
                Action::make('edit')
                    ->label('編集')
                    ->button()
                    ->url(function (MstShopItem $record) {
                        return EditUserShopItem::getUrl([
                            'userId' => $this->userId,
                            'mstShopItemId' => $record->id,
                        ]);
                    })
                    ->visible(fn () => EditUserShopItem::canAccess()),
            ], position: ActionsPosition::BeforeColumns);
    }
}
