<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstQuest;
use App\Models\Usr\UsrStageEvent;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserStageEvent extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-stage-event';

    public string $currentTab = UserSearchTabs::EVENT_QUEST->value;

    public string $userId = '';
    public string $mstQuestId = '';

    protected $queryString = [
        'userId',
        'mstQuestId'
    ];

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table): Table
    {
        $stageIds = MstQuest::query()
            ->select('mst_stages.id')
            ->join('mst_stages', 'mst_stages.mst_quest_id', '=', 'mst_quests.id')
            ->where('mst_quests.id', $this->mstQuestId)
            ->get()
            ->toArray();

        $query = UsrStageEvent::query()
            ->with([
                'mst_stage.mst_stage_i18n',
                'mst_stage.mst_quests',
                'mst_stage.mst_quests.mst_quest_i18n',
            ])
            ->where('usr_user_id', $this->userId)
            ->whereIn('mst_stage_id', $stageIds)
            ->orderBy('created_at', 'desc');

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('mst_stage_id')
                    ->label('ステージID'),
                TextColumn::make('quest')
                    ->label('クエスト')
                    ->getStateUsing(function ($record) {
                        return $record->mst_stage->mst_quests ? '[' . $record->mst_stage->mst_quests->id . '] ' . ($record->mst_stage->mst_quests->mst_quest_i18n?->name ?? '') : '';
                    }),
                TextColumn::make('mst_stage.mst_stage_i18n.name')
                    ->label('ステージ名'),
                TextColumn::make('clear_count')
                    ->label('クリア回数'),
                TextColumn::make('reset_clear_count')
                    ->label('リセットからのクリア回数'),
                TextColumn::make('reset_ad_challenge_count')
                    ->label('リセットからの広告視聴での挑戦回数'),
                TextColumn::make('latest_reset_at')
                    ->label('リセット日時'),
                TextColumn::make('created_at')
                    ->label('ステージ開放日時'),
                TextColumn::make('updated_at')
                    ->label('最終更新日時'),
            ])
            ->filters([
                Filter::make('quest_id')
                    ->label('クエストID')
                    ->form([
                        TextInput::make('quest_id')
                            ->label('クエストID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['quest_id'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('mst_stages', function ($query) use ($data) {
                                $query->whereHas('mst_quests', function ($query) use ($data) {
                                    $query->where('id', 'like', "%{$data['quest_id']}%");
                            });
                        });
                    }),
                Filter::make('stage_id')
                    ->label('ステージID')
                    ->form([
                        TextInput::make('stage_id')
                            ->label('ステージID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['stage_id'])) {
                            return $query;
                        }

                        return $query->where('mst_stage_id', $data['stage_id']);
                    }),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )->actions([
                Action::make('edit')
                    ->label('編集')
                    ->button()
                    ->url(function (UsrStageEvent $record) {
                        return EditUserStageEvent::getUrl([
                            'userId' => $this->userId,
                            'mstStageId' => $record->mst_stage_id,
                            'mstQuestId' => $record->mst_stage->mst_quests->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);;
    }
}
