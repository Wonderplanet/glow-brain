<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Domain\Stage\Enums\QuestType;
use App\Filament\Pages\User\UserDataBasePage;
use App\Filament\Pages\User\UserSearch;
use App\Models\Mst\MstQuest;
use App\Models\Usr\UsrStageEnhance;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Concerns\InteractsWithTable;

class UserEnhanceQuest extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-enhance-quest';

    public string $currentTab = UserSearchTabs::ENHANCE_QUEST->value;

    public array $userStages = [];

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function getStageTable(): Table
    {
        return $this->table($this->getTable());
    }

    private function table(Table $table): Table
    {
        $query = MstQuest::query()
            ->with([
                'mst_quest_i18n',
                'mst_stages',
                'mst_stages.mst_stage_i18n',
            ])
            ->where('quest_type', QuestType::ENHANCE);

        $usrStageEnhance = UsrStageEnhance::query()
            ->where('usr_user_id', $this->userId)
            ->get()
            ->keyBy('mst_stage_id');

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('mst_stages.id')
                    ->label('ステージID'),
                TextColumn::make('quest')
                    ->label('クエスト')
                    ->getStateUsing(function ($record) {
                        return $record ? '[' . $record->id . '] ' . ($record->mst_quest_i18n?->name ?? '') : '';
                    }),
                TextColumn::make('mst_stages.mst_stage_i18n.name')
                    ->label('ステージ名'),
                TextColumn::make('clear_count')
                    ->label('クリア回数')
                    ->getStateUsing(function ($record) use ($usrStageEnhance) {
                        $mstStage = json_decode($record->mst_stages, true);
                        return $usrStageEnhance->get($mstStage[0]['id'])?->clear_count;
                    }),
                TextColumn::make('reset_challenge_count')
                    ->label('通常の挑戦回数')
                    ->getStateUsing(function ($record) use ($usrStageEnhance) {
                        $mstStage = json_decode($record->mst_stages, true);
                        return $usrStageEnhance->get($mstStage[0]['id'])?->reset_challenge_count;
                    }),
                TextColumn::make('reset_ad_challenge_count')
                    ->label('広告視聴による挑戦回数')
                    ->getStateUsing(function ($record) use ($usrStageEnhance) {
                        $mstStage = json_decode($record->mst_stages, true);
                        return $usrStageEnhance->get($mstStage[0]['id'])?->reset_ad_challenge_count;
                    }),
                TextColumn::make('max_score')
                    ->label('最大スコア')
                    ->getStateUsing(function ($record) use ($usrStageEnhance) {
                        $mstStage = json_decode($record->mst_stages, true);
                        return $usrStageEnhance->get($mstStage[0]['id'])?->max_score;
                    }),
                TextColumn::make('latest_reset_at')
                    ->label('最終リセット日時')
                    ->getStateUsing(function ($record) use ($usrStageEnhance) {
                        $mstStage = json_decode($record->mst_stages, true);
                        return $usrStageEnhance->get($mstStage[0]['id'])?->latest_reset_at;
                    }),
            ])
            ->filters([
                Filter::make('id')
                    ->label('クエストID')
                    ->form([
                        TextInput::make('id')
                            ->label('クエストID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', $data['id']);
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('クエスト名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_quest_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('stage_id')
                    ->form([
                        TextInput::make('stage_id')
                            ->label('ステージID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['stage_id'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('mst_stages', function ($query) use ($data) {
                                $query->where('id', $data['stage_id']);
                        });
                    }),
            ], FiltersLayout::AboveContent)
            ->actions([
                Action::make('edit')->label('編集')
                    ->button()
                    ->url(function (MstQuest $mstQuest) {
                        $mstStage = json_decode($mstQuest?->mst_stages, true);
                        return EditUserStageEnhance::getUrl([
                            'userId' => $this->userId,
                            'stageId' => $mstStage[0]['id'],
                        ]);
                    })
                    ->visible(fn () => EditUserStageEnhance::canAccess()),
            ], position: ActionsPosition::BeforeColumns)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            );
    }
}
