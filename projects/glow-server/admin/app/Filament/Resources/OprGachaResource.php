<?php

namespace App\Filament\Resources;

use App\Constants\GachaType;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Pages\OprGachaDetail;
use App\Filament\Resources\OprGachaResource\Pages;
use App\Models\Mst\OprGacha;
use App\Tables\Columns\PeriodStatusTableColumn;
use App\Tables\Filters\PeriodStatusTableSelectFilter;
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

class OprGachaResource extends Resource
{

    protected static ?string $model = OprGacha::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'ガシャ';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::GACHA_DISPLAY_ORDER->value; // メニューの並び順

    public static function table(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                PeriodStatusTableColumn::make('period_status')->label('開催ステータス'),
                TextColumn::make('id')->label('ガシャID')->sortable(),
                TextColumn::make('opr_gacha_i18n.name')->label('ガシャ名')->sortable(),
                TextColumn::make('gacha_type_label')->label('ガシャタイプ')->sortable(
                    query: fn (Builder $query) => $query->orderBy('gacha_type'),
                ),
                TextColumn::make('upper_group')->label('天井グループ')->sortable(),
                TextColumn::make('start_at')->label('開始日時')->sortable(),
                TextColumn::make('end_at')->label('終了日時')->sortable(),
            ])
            ->filters([
                SelectFilter::make('gacha_type')
                    ->label('ガシャタイプ')
                    ->options(GachaType::labels()),
                Filter::make('name')->label('ガシャ名')
                    ->form([
                        TextInput::make('name')->label('ガシャ名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }

                        return $query->whereHas('opr_gacha_i18n', function (Builder $query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('upper_group')->label('天井グループ')
                    ->form([
                        TextInput::make('upper_group')->label('天井グループ')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['upper_group'])) {
                            return $query;
                        }

                        return $query->where('upper_group', 'like', "%{$data['upper_group']}%");
                    }),
                PeriodStatusTableSelectFilter::make('period_status')->label('開催ステータス'),

                Filter::make('display_gacha_caution_id')->label('ガシャ注意事項ID')
                    ->form([
                        TextInput::make('display_gacha_caution_id')->label('ガシャ注意事項ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['display_gacha_caution_id'])) {
                            return $query;
                        }

                        return $query->where('display_gacha_caution_id', $data['display_gacha_caution_id']);
                    }),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action->label('適用'),
            )
            ->actions([
                Action::make('opr_gacha_detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (OprGacha $record) {
                        return OprGachaDetail::getUrl([
                            'oprGachaId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
            ])
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
            'index' => Pages\ListOprGachas::route('/'),
        ];
    }
}
