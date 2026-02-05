<?php

namespace App\Filament\Resources;

use App\Constants\EmblemType;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Pages\EmblemDetail;
use App\Filament\Resources\MstEmblemResource\Pages;
use App\Models\Mst\MstEmblem;
use App\Tables\Columns\AssetImageColumn;
use App\Tables\Columns\MstSeriesInfoColumn;
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

class MstEmblemResource extends Resource
{

    protected static ?string $model = MstEmblem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'エンブレム';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::EMBLEM_DISPLAY_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        $query = MstEmblem::query()
        ->with([
            'mst_emblem_i18n',
            'mst_series',
            'mst_series.mst_series_i18n'
        ]);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_emblem_i18n.name')
                    ->label('エンブレム名称')
                    ->searchable()
                    ->sortable(),
                AssetImageColumn::make('asset_image')->label('エンブレム画像'),
                TextColumn::make('emblem_type')
                    ->label('エンブレムタイプ')
                    ->searchable()
                    ->sortable(),
                MstSeriesInfoColumn::make('mst_series_info')
                    ->label('作品ID')
                    ->searchable()
                    ->getStateUsing(
                        function (MstEmblem $model) {
                            return $model->mst_series ?? '';
                        }
                    ),
                TextColumn::make('asset_key')
                    ->label('アセットキー')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('release_key')
                    ->label('リリースキー')
                    ->searchable()
                    ->sortable(),
            ])
            ->searchable(false)
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
                        return $query->where('id', 'like', "{$data['id']}%");
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('エンブレム名称')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_emblem_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "{$data['name']}%");
                        });
                    }),
                SelectFilter::make('emblem_type')
                    ->options(EmblemType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('emblem_type', $data);
                    })
                    ->label('エンブレムタイプ'),
                Filter::make('mst_series_id')
                    ->form([
                        TextInput::make('mst_series_id')
                            ->label('作品ID')
                    ])
                    ->label('作品ID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['mst_series_id'])) {
                            return $query;
                        }
                        return $query->where('mst_series_id', 'like', "%{$data['mst_series_id']}%");
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
                        return $query->where('release_key', "{$data['release_key']}");
                    }),
            ], FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return EmblemDetail::getUrl([
                            'mstEmblemId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->emptyStateActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstEmblems::route('/'),
        ];
    }
}
