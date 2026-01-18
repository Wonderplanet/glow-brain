<?php

namespace App\Filament\Resources;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Pages\EventDetail;
use App\Filament\Resources\MstEventResource\Pages;
use App\Models\Mst\MstEvent;
use App\Tables\Columns\MstEventInfoColumn;
use App\Tables\Columns\MstSeriesInfoColumn;
use App\Utils\TimeUtil;
use Filament\Forms\Components\DateTimePicker;
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

class MstEventResource extends Resource
{

    protected static ?string $model = MstEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'イベント';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::EVENT_DISPLAY_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        $query = MstEvent::query()
        ->with([
            'mst_event_i18n',
        ]);

        return $table
            ->columns([
                MstEventInfoColumn::make('event_info')
                    ->label('イベント情報')
                    ->searchable()
                    ->getStateUsing(
                        function (MstEvent $model) {
                            return $model ?? '';
                        }
                    ),
                MstSeriesInfoColumn::make('mst_series_info')
                    ->label('作品情報')
                    ->searchable()
                    ->getStateUsing(
                        function (MstEvent $model) {
                            return $model->mst_series ?? '';
                        }
                    ),
                TextColumn::make('start_at')
                    ->label('開始日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('終了日')
                    ->searchable()
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
                            ->label('イベントID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', "{$data['id']}");
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('イベント名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_event_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('start_at')
                    ->form([
                        DateTimePicker::make('start_at')
                            ->label('開始日')
                            ->withoutTime()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return TimeUtil::addWhereBetweenByDay($query, 'start_at', $data);
                    }),
                Filter::make('end_at')
                    ->form([
                        DateTimePicker::make('end_at')
                            ->label('終了日')
                            ->withoutTime()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return TimeUtil::addWhereBetweenByDay($query, 'end_at', $data);
                    }),
            ], FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return EventDetail::getUrl([
                            'mstEventId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->emptyStateActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstEvents::route('/'),
        ];
    }
}
