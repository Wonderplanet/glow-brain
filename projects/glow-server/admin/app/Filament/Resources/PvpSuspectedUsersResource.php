<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use App\Filament\Pages\PvpSuspectedUserList;
use App\Filament\Resources\PvpSuspectedUsersResource\Pages;
use App\Models\Usr\SysPvpSeason;
use App\Tables\Columns\PeriodStatusTableColumn;
use App\Tables\Filters\PeriodStatusTableSelectFilter;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PvpSuspectedUsersResource extends Resource
{
    use Authorizable;

    protected static ?string $model = SysPvpSeason::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'BAN';
    protected static ?string $modelLabel = 'ランクマッチ不正疑惑一覧';

    public static function table(Table $table): Table
    {
        $now = CarbonImmutable::now();

        $query = SysPvpSeason::query()
            ->where('start_at', '<=', $now)
            ->orderBy('start_at', 'desc');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                PeriodStatusTableColumn::make('period_status')
                    ->label('開催ステータス'),
                TextColumn::make('id')
                    ->label('シーズンID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_pvp_id')
                    ->label('ランクマッチID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_at')
                    ->label('シーズン開始日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('シーズン終了日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('シーズン終了後のクローズ日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('id')
                    ->form([
                        TextInput::make('id')->label('シーズンID')
                    ])
                    ->label('シーズンID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                PeriodStatusTableSelectFilter::make('period_status')->label('開催ステータス'),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('detail')
                ->label('不正疑惑ユーザー一覧')
                ->button()
                ->url(function (SysPvpSeason $record) {
                    return PvpSuspectedUserList::getUrl([
                        'sysPvpSeasonId' => $record->id,
                    ]);
                }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPvpSuspectedUsers::route('/'),
        ];
    }
}
