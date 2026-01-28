<?php

namespace App\Filament\Pages;

use App\Constants\LogAdventBattleResult;
use App\Constants\UserSearchTabs;
use App\Domain\Pvp\Enums\LogPvpResult;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Log\LogPvpAction;
use App\Traits\AthenaQueryTrait;
use App\Traits\LogInGameBattleTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserLogPvpAction extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use LogInGameBattleTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-pvp-action';
    public string $currentTab = UserSearchTabs::LOG_PVP_ACTION->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogPvpAction::query()
            ->with([
                'sys_pvp_season',
                'opponent_user_profile',
            ])
            ->where('usr_user_id', $this->userId)
            ->orderBy('created_at', 'desc');

        $columns = [
            TextColumn::make('nginx_request_id')
                ->label('APIリクエストID')
                ->searchable()
                ->sortable(),
            TextColumn::make('sys_pvp_season_id')
                ->label('ランクマッチシーズンID')
                ->searchable()
                ->sortable(),
            TextColumn::make('sys_pvp_season.mst_pvp_id')
                ->label('ランクマッチID')
                ->searchable()
                ->sortable(),
            TextColumn::make('result')
                ->label('ランクマッチ結果')
                ->searchable()
                ->sortable()
                ->getStateUsing(
                    function ($record) {
                        return LogPvpResult::tryFrom($record->result)?->label() ?? '不明';
                    }
                ),
            TextColumn::make('opponent_my_id')
                ->label('対戦相手マイID')
                ->sortable(),
            TextColumn::make('opponent_user_profile.name')
                ->label('対戦相手ユーザー名')
                ->sortable(),
            $this->getInGameBattleColumn(['discovered_enemies', 'party_status'])
        ];

        return $table
            ->query($query)
            ->searchable(false)
            ->columns($columns)
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        SelectFilter::make('sys_pvp_season_id')
                            ->form([
                                TextInput::make('sys_pvp_season_id')
                                    ->label('ランクマッチシーズンID')
                            ])
                            ->query(function (Builder $query, $data): Builder {
                                if (blank($data['sys_pvp_season_id'])) {
                                    return $query;
                                }
                                return $query->where('sys_pvp_season_id', $data['sys_pvp_season_id']);
                            })
                            ->label('ランクマッチシーズンID'),
                        SelectFilter::make('result')
                            ->options(LogAdventBattleResult::labels()->toArray())
                            ->query(function (Builder $query, $data): Builder {
                                if (blank($data['value'])) {
                                    return $query;
                                }
                                return $query->where('result', $data['value']);
                            })
                            ->label('ランクマッチ結果'),
                    ]
                ),
                FiltersLayout::AboveContent)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_pvp_action')
            ])
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function ($record) {
                        return UserLogPvpActionDetail::getUrl([
                            'userId' => $this->userId,
                            'logPvpActionId' => $record->id
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
    }

}
