<?php

namespace App\Filament\Resources;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\ProductType;
use App\Filament\Authorizable;
use App\Filament\Pages\OprProductDetail;
use App\Filament\Resources\OprProductResource\Pages;
use App\Models\Opr\OprProduct;
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

class OprProductResource extends Resource
{

    protected static ?string $model = OprProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $modelLabel = '課金ショップ';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::PRODUCT_DISPLAY_ORDER->value; // メニューの並び順

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
                TextColumn::make('mst_store_product_id')
                    ->label('ストア商品ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product_type_label') // OprProductモデルのggetProductTypeLabelAttributeが呼ばれる
                    ->label('商品タイプ')
                    ->tooltip(fn (OprProduct $oprProduct) => $oprProduct->product_type)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchasable_count')
                    ->label('購入可能回数')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function (OprProduct $oprProduct) {
                            return $oprProduct->purchasable_count ?? '無制限';
                        }
                    ),
                TextColumn::make('paid_amount')
                    ->label('有償プリズム付与数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_priority')
                    ->label('表示優先度')
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
                    ->label('商品ID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                SelectFilter::make('product_type')
                    ->options(ProductType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('product_type', $data);
                    })
                    ->label('商品タイプ'),
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
                        return OprProductDetail::getUrl([
                            'productSubId' => $record->id,
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
            'index' => Pages\ListOprProducts::route('/'),
        ];
    }
}
