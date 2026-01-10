<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Resources\MstUserLevelResource\Pages;
use App\Models\Mst\MstUserLevel;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\RewardInfoGetTrait;

class MstUserLevelResource extends Resource
{
    use RewardInfoGetTrait;

    protected static ?string $model = MstUserLevel::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'リーダーレベル';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::LEADER_LEVEL_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        $query = MstUserLevel::query()
            ->from('mst_user_levels')
            ->leftJoin('mst_user_levels as t2', 't2.level', '=', DB::raw('mst_user_levels.level + 1'))
            ->select('mst_user_levels.*')
            ->selectRaw('(t2.exp - mst_user_levels.exp) as diff_exp');

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label('レベル')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stamina')
                    ->label('スタミナ最大値')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exp')
                    ->label('必要EXP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('diff_exp')
                    ->label('次レベルまでのEXP')
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('reward_info')
                    ->label('達成報酬情報')
                    ->getStateUsing(
                        function ($record) {
                            return RewardInfoGetTrait::getRewardInfos($record->getRewardDtos());
                        }
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('release_key')
                    ->label('リリースキー')
                    ->searchable()
                    ->sortable(),
            ])
            ->searchable(false)
            ->defaultSort('level', 'asc')
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
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
                        return $query->where('mst_user_levels.id', 'like', "{$data['id']}%");
                    }),
                Filter::make('level')
                    ->form([
                        TextInput::make('level')
                            ->label('レベル')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['level'])) {
                            return $query;
                        }
                        return $query->where('mst_user_levels.level', "{$data['level']}");
                    }),
                Filter::make('release_key')
                    ->form([
                        TextInput::make('release_key')
                            ->label('リリースキー')
                    ])
                    ->label('リリースキー')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['release_key'])) {
                            return $query;
                        }
                        return $query->where('mst_user_levels.release_key', "{$data['release_key']}");
                    }),
            ], FiltersLayout::AboveContent)
            ->emptyStateActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstUserLevels::route('/'),
        ];
    }
}
