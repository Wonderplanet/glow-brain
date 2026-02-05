<?php

namespace App\Filament\Pages;

use App\Constants\PvpTab;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Filament\Pages\Pvp\MstPvpBasePage;
use App\Models\Mst\MstPvp;
use App\Tables\Columns\PvpPeriodColumn;
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

class MstPvps extends MstPvpBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-pvps';
    public string $currentTab = PvpTab::PVP_LIST->value;
    protected static ?string $title = PvpTab::PVP_LIST->value;

    public static function table(Table $table): Table
    {
        $query = MstPvp::query();
        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                PvpPeriodColumn::make('period')
                    ->label('開催期間')
                    ->sortable(),
                TextColumn::make('ranking_min_pvp_rank_class')
                    ->label('ランキングに含む最小PVPランク区分')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('max_daily_challenge_count')
                    ->label('1日のアイテム消費なし挑戦可能回数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('max_daily_item_challenge_count')
                    ->label('1日のアイテム消費あり挑戦可能回数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item_challenge_cost_amount')
                    ->label('アイテム消費あり挑戦時の消費アイテム数')
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
                SelectFilter::make('ranking_min_pvp_rank_class')
                    ->options(PvpRankClassType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('ranking_min_pvp_rank_class', $data['value']);
                    })
                    ->label('ランキングに含む最小PVPランク区分'),
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
                    return MstPvpDetail::getUrl([
                        'mstPvpId' => $record->id,
                    ]);
                }),
            ], position: ActionsPosition::BeforeColumns);
    }
}
