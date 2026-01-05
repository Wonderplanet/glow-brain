<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Domain\Stage\Enums\QuestType;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstQuest;
use App\Tables\Columns\MstSeriesInfoColumn;
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
use Illuminate\Database\Eloquent\Model;

class UserEvent extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-event';

    public string $currentTab = UserSearchTabs::EVENT_QUEST->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table): Table
    {
        $query = MstQuest::query()
            ->with([
                'mst_event',
                'mst_event.mst_event_i18n',
                'mst_event.mst_series',
            ])
            ->where('quest_type', QuestType::EVENT)
            ->orderBy('start_date', 'DESC');

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('event')
                    ->label('イベント')
                    ->getStateUsing(function ($record) {
                        return '[' . $record->mst_event->id . ']' . $record->mst_event->mst_event_i18n->name;
                    }),
                MstSeriesInfoColumn::make('mst_series_info')
                    ->label('作品ID')
                    ->searchable()
                    ->getStateUsing(
                        function ($record) {
                            return $record->mst_event->mst_series;
                        }
                    ),
                TextColumn::make('mst_event.start_at')
                    ->label('開始日時'),
                TextColumn::make('mst_event.end_at')
                    ->label('終了日時'),
            ])
            ->filters([
                Filter::make('event_id')
                    ->form([
                        TextInput::make('event_id')
                            ->label('イベントID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['event_id'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('mst_event', function ($query) use ($data) {
                                $query->where('id', 'like', "%{$data['event_id']}%");
                        });
                    }),
                Filter::make('event_name')
                    ->form([
                        TextInput::make('event_name')
                            ->label('イベント名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['event_name'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('mst_event', function ($query) use ($data) {
                                $query->whereHas('mst_event_i18n', function ($query) use ($data) {
                                    $query->where('name', 'like', "%{$data['event_name']}%");
                                });
                        });
                    }),
                Filter::make('mst_series_id')
                    ->form([
                        TextInput::make('mst_series_id')
                            ->label('作品ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['mst_series_id'])) {
                            return $query;
                        }
                        return $query->where('mst_series_id', 'like', "%{$data['mst_series_id']}%");
                    }),
                Filter::make('series_name')
                    ->form([
                        TextInput::make('series_name')
                            ->label('作品名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['series_name'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('mst_series', function ($query) use ($data) {
                                $query->whereHas('mst_series_i18n', function ($query) use ($data) {
                                    $query->where('name', 'like', "%{$data['series_name']}%");
                                });
                        });
                    }),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )->actions([
                    Action::make('detail')
                ->label('詳細')
                ->button()
                ->url(function (Model $record) {
                    return UserStageEvent::getUrl([
                        'userId' => $this->userId,
                        'mstQuestId' => $record->id,
                    ]);
                })
                ->visible(fn () => UserStageEvent::canAccess()),
            ], position: ActionsPosition::BeforeColumns);
    }
}
