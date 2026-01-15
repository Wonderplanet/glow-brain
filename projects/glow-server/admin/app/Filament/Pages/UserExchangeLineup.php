<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrExchangeLineup;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\PageTrait;
use App\Traits\RewardInfoGetTrait;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class UserExchangeLineup extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use RewardInfoGetTrait;
    use PageTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-exchange-lineup';

    public string $currentTab = UserSearchTabs::EXCHANGE->value;

    public function mount(): void
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => UserSearchTabs::EXCHANGE->value,
        ]);
    }

    public function getTableRecords(): Paginator | CursorPaginator
    {
        return $this->augmentPaginatorWithCallback(
            function (Paginator | CursorPaginator $paginator) {
                $collection = $paginator->getCollection();

                // ページネートされたレコードのlineup IDを収集
                $lineupIds = $collection->pluck('mst_exchange_lineup_id')->unique()->filter();

                // MstExchangeLineupを一括取得
                $mstLineups = \App\Models\Mst\MstExchangeLineup::query()
                    ->with(['rewards', 'costs'])
                    ->whereIn('id', $lineupIds)
                    ->get()
                    ->keyBy('id');

                // 報酬とコストのDTOを収集
                $rewardDtos = collect();
                $costDtos = collect();

                foreach ($mstLineups as $mstLineup) {
                    if ($mstLineup->rewards && $mstLineup->rewards->isNotEmpty()) {
                        foreach ($mstLineup->rewards as $mstExchangeReward) {
                            $rewardDtos->push($mstExchangeReward->reward);
                        }
                    }

                    if ($mstLineup->costs && $mstLineup->costs->isNotEmpty()) {
                        foreach ($mstLineup->costs as $mstExchangeCost) {
                            $costDtos->push($mstExchangeCost->cost);
                        }
                    }
                }

                // RewardInfoを一括取得
                $rewardInfos = $rewardDtos->isEmpty()
                    ? collect()
                    : $this->getRewardInfos($rewardDtos);

                $costInfos = $costDtos->isEmpty()
                    ? collect()
                    : $this->getRewardInfos($costDtos);

                // 各レコードにRewardInfoを設定
                $collection = $collection->map(function ($record) use ($mstLineups, $rewardInfos, $costInfos) {
                    $mstLineup = $mstLineups->get($record->mst_exchange_lineup_id);

                    if ($mstLineup === null) {
                        $record->group_id_value = '-';
                        $record->tradable_count_value = '-';
                        $record->rewards_info = collect();
                        $record->costs_info = collect();
                        return $record;
                    }

                    // マスターデータ値を設定
                    $record->group_id_value = $mstLineup->group_id;
                    $record->tradable_count_value = $mstLineup->tradable_count ?? '無制限';

                    // 報酬情報を設定
                    $rewardInfoList = collect();
                    if ($mstLineup->rewards && $mstLineup->rewards->isNotEmpty()) {
                        foreach ($mstLineup->rewards as $mstExchangeReward) {
                            $rewardInfo = $rewardInfos->get($mstExchangeReward->reward->getId());
                            if ($rewardInfo !== null) {
                                $rewardInfoList->push($rewardInfo);
                            }
                        }
                    }
                    $record->rewards_info = $rewardInfoList;

                    // コスト情報を設定
                    $costInfoList = collect();
                    if ($mstLineup->costs && $mstLineup->costs->isNotEmpty()) {
                        foreach ($mstLineup->costs as $mstExchangeCost) {
                            $costInfo = $costInfos->get($mstExchangeCost->cost->getId());
                            if ($costInfo !== null) {
                                $costInfoList->push($costInfo);
                            }
                        }
                    }
                    $record->costs_info = $costInfoList;

                    return $record;
                });

                $paginator->setCollection($collection);
            }
        );
    }

    public function table(Table $table): Table
    {
        $query = UsrExchangeLineup::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('ユーザー交換ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_exchange_lineup_id')
                    ->label('交換ラインナップID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_exchange_id')
                    ->label('交換所ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group_id_value')
                    ->label('グループID')
                    ->searchable(),
                RewardInfoColumn::make('rewards_info')
                    ->label('報酬情報'),
                RewardInfoColumn::make('costs_info')
                    ->label('コスト情報'),
                TextColumn::make('trade_count')
                    ->label('交換回数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tradable_count_value')
                    ->label('交換上限数')
                    ->searchable(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('編集')
                    ->button()
                    ->url(function (UsrExchangeLineup $record) {
                        return EditUserExchangeLineup::getUrl([
                            'userId' => $this->userId,
                            'mstExchangeId' => $record->mst_exchange_id,
                            'mstExchangeLineupId' => $record->mst_exchange_lineup_id,
                        ]);
                    })
                    ->visible(fn () => EditUserExchangeLineup::canAccess()),
            ], position: ActionsPosition::BeforeColumns);
    }
}
