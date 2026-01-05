<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use App\Constants\UserSearchTabs;
use App\Models\Log\LogReceiveMessageReward;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\RewardInfoGetTrait;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;

class UserLogReceiveMessageReward extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-receive-message-reward';
    public string $currentTab = UserSearchTabs::LOG_GIFT->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogReceiveMessageReward::query()
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
                RewardInfoColumn::make('reward_info')
                    ->label('報酬情報')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getReceivedRewardDtos());
                        }
                    )
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('before_reward_info')
                    ->label('報酬情報(変換前)')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getBeforeReceivedRewardDtos());
                        }
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('入手日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                $this->getCommonLogFilters(),
                FiltersLayout::AboveContent)
                ->deferFilters()
                ->filtersApplyAction(
                    fn(Action $action) => $action
                    ->label('検索'),
                )
                ->defaultSort('created_at', 'desc')
                ->headerActions([
                    SimpleCsvDownloadAction::make()
                        ->fileName('user_log_receive_message_reward')
                ]);
    }
}
