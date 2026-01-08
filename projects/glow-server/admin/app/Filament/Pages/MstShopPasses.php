<?php

namespace App\Filament\Pages;

use App\Constants\PassEffectType;
use App\Constants\ShopTabs;
use App\Filament\Pages\Shop\ShopDataBasePage;
use App\Models\Mst\MstShopPass;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class MstShopPasses extends ShopDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-shop-passes';
    public string $currentTab = ShopTabs::SHOP_PASS->value;
    protected static ?string $title = ShopTabs::SHOP_PASS->value;

    public static function table(Table $table): Table
    {
        $query = MstShopPass::query()
        ->with([
            'mst_shop_pass_i18n',
            'mst_shop_pass_effect',
            'mst_shop_pass_rewards',
            'opr_product',
        ]);

        return $table
            ->query($query)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('パスID')
                    ->sortable(),
                TextColumn::make('mst_shop_pass_i18n.name')
                    ->label('パス名')
                    ->sortable(),
                TextColumn::make('mst_shop_pass_effect.effect_type')
                    ->label('効果種別')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            $data = [];
                            foreach ($record->mst_shop_pass_effect as $mstShopPassEffect) {
                                if ($mstShopPassEffect->effect_type) {
                                    $effectType = PassEffectType::tryFrom($mstShopPassEffect->effect_type);
                                    $data[] = $effectType->label();
                                }
                            }
                            return $data;
                        }
                    ),
                TextColumn::make('opr_product.paid_amount')
                    ->label('有償プリズム配布数')
                    ->sortable(),
                TextColumn::make('opr_product.start_date')
                    ->label('開始日')
                    ->sortable(),
                TextColumn::make('opr_product.end_date')
                    ->label('終了日')
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
                            ->label('パスID')
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
                            ->label('パス名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_shop_pass_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('パス名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_shop_pass_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                SelectFilter::make('effect_type')
                    ->options(PassEffectType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_shop_pass_effect', function ($query) use ($data) {
                            $query->where('effect_type', $data);
                        });
                    })
                    ->label('効果種別'),
            ],FiltersLayout::AboveContent)
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return MstShopPassDetail::getUrl([
                            'mstShopPassId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
    }
}
