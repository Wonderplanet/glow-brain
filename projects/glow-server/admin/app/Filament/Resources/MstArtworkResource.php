<?php

namespace App\Filament\Resources;

use App\Constants\ImagePath;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\RarityType;
use App\Filament\Authorizable;
use App\Filament\Pages\MstArtworkDetail;
use App\Filament\Resources\MstArtworkResource\Pages;
use App\Models\Mst\MstArtwork;
use App\Models\Mst\MstSeries;
use App\Services\AssetService;
use App\Tables\Columns\AssetImageColumn;
use App\Tables\Columns\MstSeriesInfoColumn;
use App\Utils\AssetUtil;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MstArtworkResource extends Resource
{

    protected static ?string $model = MstArtwork::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = '原画';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::ARTWORK_DISPLAY_ORDER->value; // メニューの並び順

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $query = MstArtwork::query()
        ->with([
            'mst_artwork_i18n',
            'mst_series',
            'mst_series.mst_series_i18n',
        ]);

        $mstSeries = MstSeries::query()
            ->get()
            ->keyBy(function (MstSeries $mstSeries) {
                return $mstSeries->id;
            });

        $assetService = app(AssetService::class);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                MstSeriesInfoColumn::make('mst_series_id')
                    ->label('作品ID')
                    ->searchable()
                    ->getStateUsing(
                        function (MstArtwork $model) use ($mstSeries) {
                            return $mstSeries->get($model->mst_series_id);
                        }
                    )
                    ->sortable(),
                TextColumn::make('mst_artwork_i18n.name')
                    ->label('原画名')
                    ->searchable()
                    ->sortable(),
                AssetImageColumn::make('asset_image')->label('原画画像'),
                TextColumn::make('outpost_additional_hp')
                    ->label('完成時にゲートに加算するHP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('asset_key')
                    ->label('アセットキー')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('ソート順')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('release_key')
                    ->label('リリースキー')
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
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('原画名')
                    ])
                    ->label('原画名')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_artwork_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('outpost_additional_hp_from')
                    ->form([
                        TextInput::make('outpost_additional_hp_from')
                            ->label('完成時にゲートに加算するHP FROM'),
                    ])
                    ->label('完成時にゲートに加算するHP')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['outpost_additional_hp_from'])) {
                            return $query;
                        }
                        return $query->where('outpost_additional_hp', '>=', $data['outpost_additional_hp_from']);
                    }),
                Filter::make('outpost_additional_hp_to')
                    ->form([
                        TextInput::make('outpost_additional_hp_to')
                            ->label('完成時にゲートに加算するHP TO'),
                    ])
                    ->label('完成時にゲートに加算するHP')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['outpost_additional_hp_to'])) {
                            return $query;
                        }
                        return $query->where('outpost_additional_hp', '<=', $data['outpost_additional_hp_to']);
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
                        return $query->where('release_key', $data['release_key']);
                    }),
                ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('detail')
                ->label('詳細')
                ->button()
                ->url(function (Model $record) {
                    return MstArtworkDetail::getUrl([
                        'mstArtworkId' => $record->id,
                    ]);
                }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstArtworks::route('/'),
        ];
    }
}
