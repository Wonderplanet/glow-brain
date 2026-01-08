<?php

namespace App\Filament\Resources;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Stage\Enums\TreasureRank;
use App\Filament\Authorizable;
use App\Filament\Pages\MstItemDetail;
use App\Filament\Resources\MstItemResource\Pages;
use App\Models\Mst\MstItem;
use App\Tables\Columns\AssetImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

class MstItemResource extends Resource
{

    protected static ?string $model = MstItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = 'アイテム';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::ITEM_DISPLAY_ORDER->value; // メニューの並び順

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_item_i18n.name')
                    ->label('アイテム名')
                    ->searchable()
                    ->sortable(),
                AssetImageColumn::make('asset_image')->label('アイテム画像'),
                TextColumn::make('item_type_label') // MstItemモデルのgetItemTypeLabelAttributeが呼ばれる
                    ->label('アイテムタイプ')
                    ->tooltip(fn (MstItem $mstItem) => $mstItem->getItemType())
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group_type')
                    ->label('グループタイプ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rarity')
                    ->label('レアリティ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('asset_key')
                    ->label('アセットキー')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('effect_value')
                    ->label('効果値')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('表示順')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('開始日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('終了日')
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
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('アイテム名')
                    ])
                    ->label('アイテム名')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_item_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                SelectFilter::make('type')
                    ->options(ItemType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('type', $data);
                    })
                    ->label('アイテムタイプ'),
                Filter::make('group_type')
                    ->form([
                        TextInput::make('group_type')
                            ->label('グループタイプ')
                    ])
                    ->label('グループタイプ')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['group_type'])) {
                            return $query;
                        }
                        return $query->where('group_type', 'like', "%{$data['group_type']}%");
                    }),
                SelectFilter::make('rarity')
                    ->options(TreasureRank::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('rarity', $data);
                    })
                    ->label('レアリティ'),
                SelectFilter::make('duration')
                    ->form([
                        DatePicker::make('datetime')
                                ->label('有効日時'),
                    ])
                    ->label('有効日時')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['datetime'])) {
                            return $query;
                        }
                        return $query->where('start_date', '<=', $data['datetime'])
                            ->where('end_date', '>=', $data['datetime']);
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
                        return MstItemDetail::getUrl([
                            'mstItemId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
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
            'index' => Pages\ListMstItems::route('/'),
        ];
    }
}
