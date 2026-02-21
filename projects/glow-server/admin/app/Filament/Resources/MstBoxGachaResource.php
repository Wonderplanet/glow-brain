<?php

namespace App\Filament\Resources;

use App\Constants\BoxGachaLoopType;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Pages\EventDetail;
use App\Filament\Pages\MstBoxGachaDetail;
use App\Filament\Resources\MstBoxGachaResource\Pages;
use App\Models\Mst\MstBoxGacha;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\RewardInfoGetTrait;
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

class MstBoxGachaResource extends Resource
{
    use RewardInfoGetTrait;
    protected static ?string $model = MstBoxGacha::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'BOXガシャ';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::BOX_GACHA_DISPLAY_ORDER->value;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                MstBoxGacha::query()->with([
                    'mst_event.mst_event_i18n',
                ])
            )
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')->label('BOXガシャID')->sortable(),
                TextColumn::make('mst_event.mst_event_i18n.name')
                    ->label('イベント名')
                    ->formatStateUsing(fn (MstBoxGacha $record) =>
                        $record->mst_event?->mst_event_i18n?->name ?? ''
                    )
                    ->url(fn (MstBoxGacha $record) => $record->mst_event_id !== null
                        ? EventDetail::getUrl(['mstEventId' => $record->mst_event_id])
                        : null),
                RewardInfoColumn::make('cost_info')
                    ->label('コストアイテム')
                    ->getStateUsing(
                        function ($record) {
                            return RewardInfoGetTrait::getRewardInfos($record->getCostDtos());
                        }
                    ),
                TextColumn::make('loop_type')->label('ループタイプ')->sortable()
                    ->formatStateUsing(fn (string $state): string => BoxGachaLoopType::toLabel($state)),
                TextColumn::make('mst_box_gacha_groups_count')
                    ->label('BOX数')
                    ->counts('mst_box_gacha_groups'),
            ])
            ->filters([
                Filter::make('id')->label('BOXガシャID')
                    ->form([
                        TextInput::make('id')->label('BOXガシャID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }

                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('mst_event_id')->label('イベントID')
                    ->form([
                        TextInput::make('mst_event_id')->label('イベントID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['mst_event_id'])) {
                            return $query;
                        }

                        return $query->where('mst_event_id', $data['mst_event_id']);
                    }),
                SelectFilter::make('loop_type')
                    ->label('ループタイプ')
                    ->options([
                        BoxGachaLoopType::ALL->value => '全てループ (All)',
                        BoxGachaLoopType::LAST->value => '最後のみループ (Last)',
                        BoxGachaLoopType::FIRST->value => '最初に戻る (First)',
                    ]),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action->label('適用'),
            )
            ->actions([
                Action::make('mst_box_gacha_detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (MstBoxGacha $record) {
                        return MstBoxGachaDetail::getUrl([
                            'mstBoxGachaId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
            ])
            ->emptyStateActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstBoxGachas::route('/'),
        ];
    }
}
