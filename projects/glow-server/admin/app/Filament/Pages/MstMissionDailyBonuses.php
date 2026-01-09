<?php

namespace App\Filament\Pages;

use App\Constants\MissionDailyBonusType;
use App\Constants\MissionTabs;
use App\Filament\Pages\Mission\MissionDataBasePage;
use App\Models\Mst\MstMissionDailyBonus;
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

class MstMissionDailyBonuses extends MissionDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-mission-daily-bonuses';
    public string $currentTab = MissionTabs::MISSION_DAILY_BONUS->value;
    protected static ?string $title = MissionTabs::MISSION_DAILY_BONUS->value;

    public function table(Table $table): Table
    {
        $query = MstMissionDailyBonus::query()
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
                TextColumn::make('mst_mission_daily_bonus_info')
                    ->label('ログインボーナス情報')
                    ->searchable()
                    ->getStateUsing(
                        function (MstMissionDailyBonus $mstMission) {
                            return $mstMission->type_label . $mstMission->login_day_count . '日';
                        }
                    ),
                TextColumn::make('mission_daily_bonus_type')
                    ->label('ログインボーナスタイプ')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            $missionDailyBonusType = MissionDailyBonusType::tryFrom($record->mission_daily_bonus_type);
                            return $missionDailyBonusType->label();
                        }
                    ),
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
                    Filter::make('mission_daily_bonus_type')
                        ->form([
                            TextInput::make('mission_daily_bonus_type')
                                ->label('ログインボーナスタイプ')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['mission_daily_bonus_type'])) {
                                return $query;
                            }
                            return $query->where('mission_daily_bonus_type', 'like', "%{$data['mission_daily_bonus_type']}%");
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
                    return MstMissionDailyBonusDetail::getUrl([
                        'mstMissionDailyBonusDailyId' => $record->id,
                    ]);
                }),
        ], position: ActionsPosition::BeforeColumns)
        ->emptyStateActions([
        ]);
    }
}

