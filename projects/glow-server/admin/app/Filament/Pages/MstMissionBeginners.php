<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mission\MissionDataBasePage;
use App\Models\Mst\MstMissionBeginner;
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

class MstMissionBeginners extends MissionDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-mission-beginners';
    public string $currentTab = MissionTabs::MISSION_BEGINNER->value;
    protected static ?string $title = MissionTabs::MISSION_BEGINNER->value;

    public function table(Table $table): Table
    {
        $query = MstMissionBeginner::query()
            ->with([
                'mst_mission_i18n'
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
                TextColumn::make('mst_mission_i18n.description')
                    ->label('説明')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criterion_type')
                    ->label('達成条件タイプ')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return $record->criterion_type;
                        }
                    ),
                TextColumn::make('criterion_value')
                    ->label('達成条件値')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criterion_count')
                    ->label('達成回数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unlock_day')
                    ->label('開始からの開放日')
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
                    Filter::make('description')
                        ->form([
                            TextInput::make('description')
                                ->label('説明')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['description'])) {
                                return $query;
                            }
                            return $query->whereHas('mst_mission_i18n', function ($query) use ($data) {
                                $query->where('description', 'like', "%{$data['description']}%");
                            });
                        }),
                    Filter::make('criterion_type')
                        ->form([
                            TextInput::make('criterion_type')
                                ->label('達成条件タイプ')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['criterion_type'])) {
                                return $query;
                            }
                            return $query->where('criterion_type', 'like', "%{$data['criterion_type']}%");
                        }),
                    Filter::make('unlock_day')
                        ->form([
                            TextInput::make('unlock_day')
                                ->label('開始からの開放日')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['unlock_day'])) {
                                return $query;
                            }
                            return $query->where('unlock_day', $data['unlock_day']);
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
                    return MstMissionBeginnerDetail::getUrl([
                        'mstMissionBeginnerId' => $record->id,
                    ]);
                }),
        ], position: ActionsPosition::BeforeColumns)
        ->emptyStateActions([
        ]);
    }

}
