<?php

namespace App\Filament\Pages;

use App\Constants\PvpTab;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Filament\Pages\Pvp\MstPvpBasePage;
use App\Models\Mst\MstPvpRank;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MstPvpRanks extends MstPvpBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-pvp-ranks';
    public string $currentTab = PvpTab::PVP_RANK->value;
    protected static ?string $title = PvpTab::PVP_RANK->value;

    public static function table(Table $table): Table
    {
        return $table
            ->query(MstPvpRank::query())
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                // TODO 画像のリソースできたら表示できると良さそう
                TextColumn::make('rank_class_type')
                    ->label('PVPランク区分')
                    ->sortable(),
                TextColumn::make('rank_class_level')
                    ->label('PVPランクレベル')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('required_lower_score')
                    ->label('PVPランクの最小スコア')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('win_add_point')
                    ->label('勝利時のスコア加算値')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lose_sub_point')
                    ->label('敗北時のスコア減算値')
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
                SelectFilter::make('rank_class_type')
                    ->options(PvpRankClassType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('rank_class_type', $data['value']);
                    })
                    ->label('PVPランク区分'),
                Filter::make('rank_class_level')
                    ->form([
                        TextInput::make('rank_class_level')
                            ->label('PVPランクレベル')
                    ])
                    ->label('rank_class_level')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['rank_class_level'])) {
                            return $query;
                        }
                        return $query->where('rank_class_level', 'like', "%{$data['rank_class_level']}%");
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            );
    }
}
