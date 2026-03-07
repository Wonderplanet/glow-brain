<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use App\Filament\Resources\AdventBattleSuspectedUsersResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Pages\AdventBattleSuspectedUserList;
use App\Models\Mst\MstAdventBattle;
use Filament\Tables\Enums\ActionsPosition;
use App\Tables\Columns\PeriodStatusTableColumn;
use App\Tables\Filters\PeriodStatusTableSelectFilter;
use Filament\Tables\Filters\SelectFilter;
use App\Constants\AdventBattleType;
use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonImmutable;

class AdventBattleSuspectedUsersResource extends Resource
{
    use Authorizable;

    protected static ?string $model = MstAdventBattle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'BAN';
    protected static ?string $modelLabel = '降臨バトル不正疑惑一覧';

    public static function table(Table $table): Table
    {
        $now = CarbonImmutable::now();

        $query = MstAdventBattle::query()
            ->where('start_at', '<=', $now);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                PeriodStatusTableColumn::make('period_status')
                    ->label('開催ステータス'),
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('advent_battle_type')
                    ->label('降臨バトルタイプ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_at')
                    ->label('開始日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_at')
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
                    ->label('ID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                PeriodStatusTableSelectFilter::make('period_status')->label('開催ステータス'),
                SelectFilter::make('advent_battle_type')
                    ->options(AdventBattleType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('advent_battle_type', $data);
                    })
                    ->label('降臨バトルタイプ'),
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
                ->url(function (Model $record) {
                    return AdventBattleSuspectedUserList::getUrl([
                        'mstAdventBattleId' => $record->id,
                    ]);
                }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdventBattleSuspectedUsers::route('/'),
        ];
    }
}
