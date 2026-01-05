<?php

namespace App\Filament\Resources;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Pages\MstComebackBonusDetail;
use App\Filament\Resources\MstComebackBonusScheduleResource\Pages;
use App\Models\Mst\MstComebackBonusSchedule;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MstComebackBonusScheduleResource extends Resource
{
    use Authorizable;

    protected static ?string $model = MstComebackBonusSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'カムバックボーナススケジュール';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::COMEBACK_BONUS_ORDER->value;

    public static function table(Table $table): Table
    {
        return $table
            ->hiddenFilterIndicators()
            ->query(MstComebackBonusSchedule::query())
            ->columns([
                TextColumn::make('id')
                    ->label('スケジュールID'),
                TextColumn::make('inactive_condition_days')
                    ->label('非アクティブ条件日数')
                    ->sortable()
                    ->suffix('日'),
                TextColumn::make('duration_days')
                    ->label('継続日数')
                    ->sortable()
                    ->suffix('日'),
                TextColumn::make('start_at')
                    ->label('開始日時')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('終了日時')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->searchable(false)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->filters([
                Filter::make('id')
                    ->form([
                        TextInput::make('id')
                            ->label('スケジュールID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('inactive_condition_days')
                    ->form([
                        TextInput::make('inactive_condition_days')
                            ->label('非アクティブ条件日数')
                            ->numeric()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['inactive_condition_days'])) {
                            return $query;
                        }
                        return $query->where('inactive_condition_days', '>=', $data['inactive_condition_days']);
                    }),
                Filter::make('duration_days')
                    ->form([
                        TextInput::make('duration_days')
                            ->label('継続日数')
                            ->numeric()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['duration_days'])) {
                            return $query;
                        }
                        return $query->where('duration_days', '>=', $data['duration_days']);
                    }),
                Filter::make('release_key')
                    ->form([
                        TextInput::make('release_key')
                            ->label('リリースキー')
                            ->numeric()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['release_key'])) {
                            return $query;
                        }
                        return $query->where('release_key', $data['release_key']);
                    }),
            ], FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return MstComebackBonusDetail::getUrl([
                            'mstComebackBonusScheduleId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstComebackBonusSchedules::route('/'),
        ];
    }
}
