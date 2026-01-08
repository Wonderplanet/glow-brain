<?php

namespace App\Filament\Pages;

use App\Constants\LogStageResult;
use App\Constants\LogTablePageConstants;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Filament\Pages\User\UserDataBasePage;
use App\Filament\Tables\Columns\DateTimeColumn;
use App\Models\Log\LogStageAction;
use App\Models\Mst\MstEnemyCharacter;
use App\Models\Mst\MstUnit;
use App\Tables\Columns\MstArtworkInfoColumn;
use App\Tables\Columns\MstStageInfoColumn;
use App\Traits\AthenaQueryTrait;
use App\Traits\LogInGameBattleTrait;
use App\Traits\LogUserPartyTrait;
use App\Traits\UserLogTableFilterTrait;
use App\Traits\UserResourceLogTrait;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class UserLogStageAction extends UserDataBasePage implements HasTable
{
    use LogInGameBattleTrait;
    use LogUserPartyTrait;
    use UserResourceLogTrait;
    use UserLogTableFilterTrait;
    use AthenaQueryTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-stage-action';
    public string $currentTab = UserSearchTabs::LOG_STAGE_ACTION->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        // Eager Loadingでページネーション後のレコード分だけマスタデータを取得
        $query = LogStageAction::query()
            ->with(['mst_stage', 'mst_artwork'])
            ->where('usr_user_id', $this->userId);

        // MstUnitとMstEnemyCharacterは全件キャッシュする（パーティ情報で使用）
        $mstUnits = MstUnit::query()
            ->with('mst_unit_i18n')
            ->get()
            ->keyBy('id');

        $mstEnemyCharacters = MstEnemyCharacter::query()
            ->with('mst_enemy_character_i18n')
            ->get()
            ->keyBy('id');

        $columns = [
            TextColumn::make('nginx_request_id')
                ->label('APIリクエストID')
                ->searchable()
                ->sortable(),
            MstStageInfoColumn::make('mst_stage_id')
                ->label('ステージ情報')
                ->searchable()
                ->getStateUsing(
                    function ($record) {
                        return $record->mst_stage;
                    }
                )
                ->sortable(),
            DateTimeColumn::make('created_at')
                ->label('実行日時')
                ->searchable()
                ->sortable(),
            TextColumn::make('auto_lap_count')
                ->label('スタミナブースト周回数')
                ->searchable()
                ->sortable(),
            TextColumn::make('result')
                ->label('ステージ結果')
                ->searchable()
                ->sortable()
                ->getStateUsing(
                    function ($record) {
                        $result = LogStageResult::tryFrom($record->result);
                        return $result?->label() ?? '';
                    }
                ),
            TextColumn::make('mst_outpost_id')
                ->label('使用ゲート')
                ->searchable()
                ->sortable(),
            MstArtworkInfoColumn::make('mst_artwork_id')
                ->label('装備原画')
                ->getStateUsing(
                    function ($record) {
                        return $record->mst_artwork;
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
                $this->getCommonLogFilters([LogTablePageConstants::CREATED_AT_RANGE, LogTablePageConstants::NGINX_REQUEST_ID])
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
                    ->fileName('user_log_stage_action')
            ]);
    }
}
