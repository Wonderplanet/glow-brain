<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mng\MngJumpPlusReward;
use App\Models\Usr\UsrJumpPlusReward;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\PageTrait;
use App\Traits\RewardInfoGetTrait;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class UserJumpPlusReward extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use RewardInfoGetTrait;
    use PageTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-jump-plus-reward';

    public string $currentTab = UserSearchTabs::JUMP_PLUS_REWARD->value;

    public function mount(): void
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function getTableRecords(): Paginator | CursorPaginator
    {
        return $this->augmentPaginatorWithCallback(
            function (Paginator | CursorPaginator $paginator) {
                $this->addRewardInfosColumn($paginator);
            }
        );
    }

    private function table(Table $table): Table
    {
        $query = UsrJumpPlusReward::query()
            ->where('usr_user_id', $this->userId)
            ->orderBy('created_at', 'desc');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('created_at')
                    ->label('付与日時'),
                TextColumn::make('mng_jump_plus_reward_schedule_id')
                    ->label('ジャンプ+連携報酬のスケジュールID'),
                RewardInfoColumn::make('reward_infos')
                    ->label('報酬情報'),
            ])
            ->deferFilters();
    }

    /**
     * ページネートで取得したレコードを使って一覧に必要な情報を追加する
     * @param \Illuminate\Contracts\Pagination\Paginator $paginator
     * @return void
     */

    private function addRewardInfosColumn(Paginator $paginator): void
    {
        $usrJumpPlusRewards = $paginator->getCollection();

        $rewardInfosByMngScheduleId = $usrJumpPlusRewards->mapWithKeys(function (UsrJumpPlusReward $usrJumpPlusReward) {
            $mstScheduleId = $usrJumpPlusReward->mng_jump_plus_reward_schedule_id;
            $mngRewards = $usrJumpPlusReward->mng_jump_plus_reward_schedules?->mng_jump_plus_rewards ?? collect();
            $rewardDtos = $mngRewards->map(function (MngJumpPlusReward $reward) {
                return $reward->reward;
            });
            $rewardInfos = $this->getRewardInfos($rewardDtos);

            return [
                $mstScheduleId => $rewardInfos,
            ];
        });

        $collection = $usrJumpPlusRewards->map(function ($usrJumpPlusReward) use ($rewardInfosByMngScheduleId) {
            $usrJumpPlusReward->reward_infos = $rewardInfosByMngScheduleId->get(
                $usrJumpPlusReward->mng_jump_plus_reward_schedule_id
            );
            return $usrJumpPlusReward;
        });
        $paginator->setCollection($collection);
    }
}
