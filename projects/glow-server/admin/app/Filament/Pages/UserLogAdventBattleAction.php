<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Constants\UserSearchTabs;
use App\Constants\LogAdventBattleResult;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Models\Log\LogAdventBattleAction;
use App\Models\Mst\MstArtwork;
use App\Models\Mst\MstEnemyCharacter;
use App\Models\Mst\MstUnit;
use App\Models\Mst\MstAdventBattle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\LogUserPartyTrait;
use App\Traits\LogInGameBattleTrait;
use App\Tables\Columns\MstArtworkInfoColumn;
use App\Tables\Columns\MstAdventBattleInfoColumn;

class UserLogAdventBattleAction extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use LogInGameBattleTrait;
    use LogUserPartyTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-advent-battle-action';
    public string $currentTab = UserSearchTabs::LOG_ADVENT_BATTLE_ACTION->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogAdventBattleAction::query()
            ->where('usr_user_id', $this->userId);

        $mstArtworks = MstArtwork::query()
            ->get()
            ->keyBy('id');

        $mstUnits = MstUnit::query()
            ->with('mst_unit_i18n')
            ->get()
            ->keyBy('id');

        $mstEnemyCharacters = MstEnemyCharacter::query()
            ->with('mst_enemy_character_i18n')
            ->get()
            ->keyBy('id');

        $mstAdventBattleIds = $query->pluck('mst_advent_battle_id')->toArray();
        $mstAdventBattles = MstAdventBattle::query()
            ->whereIn('id', $mstAdventBattleIds)
            ->get()
            ->keyBy('id');
    

        $columns = [
            TextColumn::make('nginx_request_id')
                ->label('APIリクエストID')
                ->searchable()
                ->sortable(),
            MstAdventBattleInfoColumn::make('mst_advent_battle_id')
                ->label('降臨バトルID')
                ->searchable()
                ->sortable()
                ->getStateUsing(
                    function ($record) use ($mstAdventBattles) {
                        return $mstAdventBattles->get($record->mst_advent_battle_id);
                    }
                ),
            TextColumn::make('result')
                ->label('降臨バトル結果')
                ->searchable()
                ->sortable()
                ->getStateUsing(
                    function ($record) {
                        $result = LogAdventBattleResult::tryFrom($record->result);
                        return $result->label();
                    }
                ),
            TextColumn::make('mst_outpost_id')
                ->label('ゲート')
                ->searchable()
                ->sortable()
                ->getStateUsing(
                    function ($record) {
                        $usedOutpost = json_decode($record->used_outpost);
                        return $usedOutpost->mst_outpost_id;
                    }
                ),
            MstArtworkInfoColumn::make('mst_artwork_id')
                ->label('原画')
                ->getStateUsing(
                    function ($record) use ($mstArtworks) {
                        $usedOutpost = json_decode($record->used_outpost);
                        return $mstArtworks->get($usedOutpost->mst_artwork_id);
                    }
                ),
        ];
        $logInGameBattleColumn = $this->getInGameBattleColumns($mstEnemyCharacters, ['discovered_enemies', 'party_status']);
        $partyInfoColumn = $this->getPartyInfoColumns($mstUnits);
        $columns = array_merge(
            $columns,
            $logInGameBattleColumn,
            $partyInfoColumn,
        );

        return $table
            ->query($query)
            ->searchable(false)
            ->columns(
                $columns
            )
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        SelectFilter::make('result')
                            ->options(LogAdventBattleResult::labels()->toArray())
                            ->query(function (Builder $query, $data): Builder {
                                if (blank($data['value'])) {
                                    return $query;
                                }
                                return $query->where('result', $data);
                            })
                            ->label('降臨バトル結果'),
                    ]
                )
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
                    ->fileName('user_log_advent_battle_action')
            ]);
    }

}
