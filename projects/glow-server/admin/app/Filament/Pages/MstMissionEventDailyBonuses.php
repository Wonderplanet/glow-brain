<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mission\MissionDataBasePage;
use App\Models\Mst\MstMissionEventDailyBonus;
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

class MstMissionEventDailyBonuses extends MissionDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-mission-event-daily-bonuses';
    public string $currentTab = MissionTabs::MISSION_EVENT_DAILY_BONUS->value;
    protected static ?string $title = MissionTabs::MISSION_EVENT_DAILY_BONUS->value;

    public function table(Table $table): Table
    {
        $query = MstMissionEventDailyBonus::query()
            ->with([
                'mst_mission_event_daily_bonus_schedule',
                'mst_mission_event_daily_bonus_schedule.mst_event',
                'mst_mission_event_daily_bonus_schedule.mst_event.mst_event_i18n',
            ])
            ->orderby('sort_order', 'asc');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_mission_event_daily_bonus_schedule.mst_event.id')
                    ->label('イベントID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_mission_event_daily_bonus_schedule.mst_event.mst_event_i18n.name')
                    ->label('イベント名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_mission_daily_bonus_info')
                    ->label('ログイン日数')
                    ->searchable()
                    ->getStateUsing(
                        function ($record) {
                            return $record->login_day_count;
                        }
                    ),
                TextColumn::make('mst_mission_event_daily_bonus_schedule.start_at')
                    ->label('開始日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_mission_event_daily_bonus_schedule.end_at')
                    ->label('終了日')
                    ->searchable()
                    ->sortable(),
                ])
            ->filters([
                Filter::make('id')
                    ->form([
                        TextInput::make('id')
                            ->label('ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('event_id')
                    ->form([
                        TextInput::make('event_id')
                            ->label('イベントID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['event_id'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_mission_event_daily_bonus_schedule', function ($query) use ($data) {
                            $query->where('mst_event_id', 'like', "%{$data['event_id']}%");
                        });
                    }),
                Filter::make('event_name')
                    ->form([
                        TextInput::make('event_name')
                            ->label('イベント名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['event_name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_mission_event_daily_bonus_schedule', function ($query) use ($data) {
                            $query->whereHas('mst_event', function ($query) use ($data) {
                                $query->whereHas('mst_event_i18n', function ($query) use ($data) {
                                    $query->where('name', 'like', "%{$data ['event_name']}%");
                                });
                            });
                        });
                    }),
                ], FiltersLayout::AboveContent)
                ->searchable(false)
                ->deferFilters()
                ->hiddenFilterIndicators()
                ->filtersApplyAction(
                    fn (Action $action) => $action
                        ->label('適用'),
                )
                ->actions([
                    Action::make('detail')
                        ->label('詳細')
                        ->button()
                        ->url(function (Model $record) {
                            return MstMissionEventDailyBonusDetail::getUrl([
                                'mstMissionEventDailyBonusDailyId' => $record->id,
                            ]);
                        }),
                ], position: ActionsPosition::BeforeColumns)
                ->emptyStateActions([
                ]);
    }
}

