<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Constants\MissionLimitedTermCategory;
use App\Filament\Pages\Mission\MissionDataBasePage;
use App\Models\Mst\MstMissionLimitedTerm;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Concerns\InteractsWithInfolists;
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
use Filament\Tables\Filters\SelectFilter;

class MstMissionLimitedTerms extends MissionDataBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-limited-terms';

    public string $currentTab = MissionTabs::MISSION_LIMITED_TERM->value;
    protected static ?string $title = MissionTabs::MISSION_LIMITED_TERM->value;

    public function table(Table $table): Table
    {
        $query = MstMissionLimitedTerm::query()
            ->with([
                'mst_mission_i18n',
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
                TextColumn::make('mission_category')
                    ->label('ミッションカテゴリ')
                    ->searchable(),
                TextColumn::make('criterion_type')
                    ->label('達成条件タイプ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criterion_value')
                    ->label('達成条件値')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criterion_count')
                    ->label('達成回数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('progress_group_key')
                    ->label('進捗グループ')
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
                Filter::make('criterion_value')
                    ->form([
                        TextInput::make('criterion_value')
                            ->label('達成条件値')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['criterion_value'])) {
                            return $query;
                        }
                        return $query->where('criterion_value', 'like', "%{$data['criterion_value']}%");
                    }),
                Filter::make('progress_group_key')
                    ->form([
                        TextInput::make('progress_group_key')
                            ->label('進捗グループ')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['progress_group_key'])) {
                            return $query;
                        }
                        return $query->where('progress_group_key', 'like', "%{$data['progress_group_key']}%");
                    }),
                SelectFilter::make('mission_category')
                    ->options(MissionLimitedTermCategory::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('mission_category', $data);
                    })
                    ->label('ミッションカテゴリ'),
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
                        return MstMissionLimitedTermsDetail::getUrl([
                            'mstMissionLimitedTermId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->emptyStateActions([
            ]);
    }

}
