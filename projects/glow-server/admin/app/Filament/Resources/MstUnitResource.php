<?php

namespace App\Filament\Resources;

use App\Constants\AttackRangeType;
use App\Constants\ImagePath;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\RarityType;
use App\Constants\RoleType;
use App\Filament\Authorizable;
use App\Filament\Pages\MstUnitDetail;
use App\Filament\Resources\MstUnitResource\Pages;
use App\Models\Mst\MstUnit;
use App\Tables\Columns\AssetImageColumn;
use App\Tables\Columns\MstSeriesInfoColumn;
use App\Utils\AssetUtil;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MstUnitResource extends Resource
{

    protected static ?string $model = MstUnit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'キャラ';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::UNIT_DISPLAY_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        $query = MstUnit::query()
        ->with([
            'mst_unit_i18n',
            'mst_series',
            'mst_series.mst_series_i18n'
        ]);

        return $table
            ->query($query)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('キャラID')
                    ->sortable(),
                TextColumn::make('mst_unit_i18n.name')
                    ->label('キャラ名称')
                    ->searchable()
                    ->sortable(),
                AssetImageColumn::make('asset_image')->label('キャラ画像'),
                TextColumn::make('role_type')
                    ->label('ロールタイプ')
                    ->sortable(),
                TextColumn::make('unit_label')
                    ->label('ユニットラベル')
                    ->sortable(),
                TextColumn::make('attack_range_type')
                    ->label('射程')
                    ->sortable(),
                TextColumn::make('rarity')
                    ->label('レアリティ')
                    ->sortable(),
                TextColumn::make('summon_cost')
                    ->label('召喚コスト')
                    ->sortable(),
                MstSeriesInfoColumn::make('mst_series_info')
                    ->label('作品ID')
                    ->searchable()
                    ->getStateUsing(
                        function (MstUnit $model) {
                            return $model->mst_series ?? '';
                        }
                    ),
                TextColumn::make('rarity')
                    ->label('レアリティ')
                    ->sortable(),
                TextColumn::make('role_type')
                    ->label('ロールタイプ')
                    ->sortable(),
                TextColumn::make('attack_range_type')
                    ->label('射程')
                    ->sortable(),
                TextColumn::make('move_speed')
                    ->label('移動速度')
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
                            ->label('キャラID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('キャラ名称')
                    ])
                    ->label('キャラ名称称')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_unit_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('mst_series_id')
                    ->form([
                        TextInput::make('mst_series_id')
                            ->label('作品ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['mst_series_id'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_series', function ($query) use ($data) {
                            $query->where('id', 'like', "%{$data['mst_series_id']}%");
                        });
                    }),
                Filter::make('series_name')
                    ->form([
                        TextInput::make('series_name')
                            ->label('作品名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['series_name'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('mst_series', function ($query) use ($data) {
                            $query->whereHas('mst_series_i18n', function ($query) use ($data) {
                                $query->where('name', 'like', "%{$data['series_name']}%");
                            });
                        });
                    }),
                SelectFilter::make('rarity')
                    ->options(RarityType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('rarity', $data);
                    })
                    ->label('レアリティ'),
                SelectFilter::make('role_type')
                    ->options(RoleType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('role_type', $data);
                    })
                    ->label('ロールタイプ'),
                SelectFilter::make('attack_range_type')
                    ->options(AttackRangeType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('attack_range_type', $data);
                    })
                    ->label('射程'),
                Filter::make('move_speed')
                    ->form([
                        TextInput::make('move_speed')
                            ->label('移動速度')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['move_speed'])) {
                            return $query;
                        }
                        return $query->where('move_speed', '>=', "{$data['move_speed']}");
                    }),
            ], FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                ->label('詳細')
                ->button()
                ->url(function (Model $record) {
                    return MstUnitDetail::getUrl([
                        'mstUnitId' => $record->id,
                    ]);
                }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstUnits::route('/'),
        ];
    }
}
