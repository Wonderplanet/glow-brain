<?php

namespace App\Filament\Pages;

use App\Constants\PvpBonusType;
use App\Constants\PvpTab;
use App\Filament\Pages\Pvp\MstPvpBasePage;
use App\Models\Mst\MstPvpBonusPoint;
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

class MstPvpBonusPoints extends MstPvpBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-pvp-bonus-points';
    public string $currentTab = PvpTab::PVP_BONUS_POINT->value;
    protected static ?string $title = PvpTab::PVP_BONUS_POINT->value;

    public static function table(Table $table): Table
    {
        return $table
            ->query(MstPvpBonusPoint::query())
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('threshold')
                    ->label('しきい値')
                    ->sortable(),
                TextColumn::make('bonus_point')
                    ->label('ボーナスポイント')
                    ->sortable(),
                TextColumn::make('bonus_type')
                    ->label('ボーナスタイプ')
                    ->sortable(),
                TextColumn::make('release_key')
                    ->label('リリースキー')
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
                SelectFilter::make('bonus_type')
                    ->options(PvpBonusType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('bonus_type', $data['value']);
                    })
                    ->label('ボーナスタイプ'),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            );
    }
}
