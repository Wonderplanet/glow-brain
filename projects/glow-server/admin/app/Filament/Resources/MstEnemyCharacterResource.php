<?php

namespace App\Filament\Resources;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\MstEnemyCharacterResource\Pages\ListMstEnemyCharacters;
use App\Models\Mst\MstEnemyCharacter;
use App\Tables\Columns\AssetImageColumn;
use App\Tables\Columns\MstIdColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MstEnemyCharacterResource extends Resource
{

    protected static ?string $model = MstEnemyCharacter::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = '敵キャラ';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::ENEMY_DISPLAY_ORDER->value;

    public static function table(Table $table): Table
    {
        $query = MstEnemyCharacter::query()->with(['mst_enemy_character_i18n']);

        return $table
            ->query($query)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')->label('敵キャラID')->sortable(),
                TextColumn::make('mst_enemy_character_i18n.name')->label('敵キャラ名')->searchable()->sortable(),
                AssetImageColumn::make('asset_image')->label('敵キャラ画像'),
                MstIdColumn::make('mst_series_info')->label('作品情報')
                    ->getMstUsing(function (MstEnemyCharacter $model) {
                        return $model->mst_series;
                    }),
                TextColumn::make('is_displayed_encyclopedia_label')
                    ->label('図鑑表示')
                    ->sortable()
                    ->badge()
                    ->color(fn(MstEnemyCharacter $record) => $record->getDisplayedEncyclopediaColor()),
            ])
            ->searchable(false)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action->label('適用'),
            )
            ->filters([
                Filter::make('id')
                    ->form([
                        TextInput::make('id')->label('敵キャラID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id']))
                            return $query;
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')->label('敵キャラ名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name']))
                            return $query;
                        return $query->whereHas('mst_enemy_character_i18n', function ($q) use ($data) {
                            $q->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('mst_series_id')
                    ->form([
                        TextInput::make('mst_series_id')->label('作品ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['mst_series_id']))
                            return $query;
                        return $query->where('mst_series_id', 'like', "%{$data['mst_series_id']}%");
                    }),
                Filter::make('is_displayed_encyclopedia')
                    ->label('図鑑表示')
                    ->form([
                        \Filament\Forms\Components\Select::make('is_displayed_encyclopedia')
                            ->label('図鑑表示')
                            ->options(MstEnemyCharacter::getDisplayedEncyclopediaOptions())
                            ->placeholder('すべて')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['is_displayed_encyclopedia'])) {
                            return $query;
                        }
                        return $query->where('is_displayed_encyclopedia', $data['is_displayed_encyclopedia']);
                    }),
            ], FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function ($record) {
                        return \App\Filament\Pages\MstEnemyCharacterDetail::getUrl([
                            'mstEnemyCharacterId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMstEnemyCharacters::route('/'),
        ];
    }
}
