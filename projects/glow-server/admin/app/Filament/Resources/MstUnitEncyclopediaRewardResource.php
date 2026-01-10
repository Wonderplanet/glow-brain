<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use App\Filament\Resources\MstUnitEncyclopediaRewardResource\Pages;
use App\Models\Mst\MstUnitEncyclopediaReward;
use App\Constants\RewardType;
use App\Constants\NavigationGroups;
use App\Tables\Columns\RewardInfoColumn;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use App\Constants\MstDataMenuDisplayOrder;

class MstUnitEncyclopediaRewardResource extends Resource
{

    protected static ?string $model = MstUnitEncyclopediaReward::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = '図鑑ランク報酬';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::UNIT_ENCYCLOPEDIA_REWARD_DISPLAY_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit_encyclopedia_rank')
                    ->label('図鑑ランク')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('resource_type')
                    ->label('報酬タイプ')
                    ->getStateUsing(
                        function ($record) {
                            $resourceTypeEnum = RewardType::tryFrom($record->resource_type);
                            return $resourceTypeEnum->label();
                        }
                    )
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('reward_info')->label('報酬情報'),
            ])
            ->searchable(false)
            ->filters([
                Filter::make('unit_encyclopedia_rank')
                    ->form([
                        TextInput::make('unit_encyclopedia_rank')
                            ->label('図鑑ランク')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['unit_encyclopedia_rank'])) {
                            return $query;
                        }
                        return $query->where('unit_encyclopedia_rank', $data['unit_encyclopedia_rank']);
                    }),
                SelectFilter::make('resource_type')
                    ->options(RewardType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('resource_type', $data);
                    })
                    ->label('報酬タイプ'),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('適用'),
            )
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstUnitEncyclopediaRewards::route('/'),
        ];
    }
}
