<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Filament\Pages\User\UserSearch;
use App\Models\Mst\MstQuest;
use App\Models\Usr\UsrStage;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserStage extends UserDataBasePage implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-stage';

    public string $currentTab = UserSearchTabs::QUEST->value;

    public array $userStages = [];

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserSearch::getUrl(['userId' => $this->userId]) => 'クエスト',
        ]);
    }

    public function getStageTable(): Table
    {
        return $this->table($this->getTable());
    }

    private function table(Table $table): Table
    {
        $query = UsrStage::query()
            ->where('usr_user_id', $this->userId)
            ->orderBy('created_at', 'desc')
            ->with([
                'mst_stages.mst_quests',
                'mst_stages',
                'mst_stages.mst_stage_i18n',
                'mst_stages.mst_quests.mst_quest_i18n'
            ]);


        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                Tables\Columns\TextColumn::make('mst_stage_id')
                    ->label('ステージID'),
                Tables\Columns\TextColumn::make('quest')
                    ->label('クエスト')
                    ->getStateUsing(function ($record) {
                        return $record->mst_stages->mst_quests ? '[' . $record->mst_stages->mst_quests->id . '] ' . ($record->mst_stages->mst_quests->mst_quest_i18n?->name ?? '') : '';
                    }),
                Tables\Columns\TextColumn::make('mst_stages.mst_stage_i18n.name')
                    ->label('ステージ名'),
                Tables\Columns\TextColumn::make('clear_count')
                    ->label('クリア回数'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('初回更新日時'),
                Tables\Columns\TextColumn::make('updated_at')
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

                        $quest = MstQuest::query()->with('mst_stages')->where('id', $data['quest_id'])->get();
                        $stageIds = $quest->map(function ($item) {
                            return $item->mst_stages->map(function ($stage) {
                                return $stage->id;
                            });
                        })->flatten()->toArray();
                        if (empty($stageIds)) {
                            return $query;
                        }

                        return $query->whereIn('mst_stage_id', $stageIds);
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
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            );
    }
}
