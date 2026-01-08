<?php

namespace App\Filament\Pages;

use App\Constants\PvpTab;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Filament\Pages\Pvp\MstPvpBasePage;
use App\Models\Mst\MstPvpDummy;
use App\Models\Mst\MstPvpRank;
use App\Tables\Columns\UsrPartyUnitInfoColumn;
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
use Illuminate\Support\Facades\Log;

class MstPvpDummies extends MstPvpBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-pvp-dummies';
    public string $currentTab = PvpTab::PVP_DUMMY->value;
    protected static ?string $title = PvpTab::PVP_DUMMY->value;

    public static function table(Table $table): Table
    {
        $query = MstPvpDummy::query()->with([
            'mst_dummy_user',
            'mst_dummy_user.mst_dummy_user_i18n',
            'mst_dummy_user.mst_dummy_user_units',
            'mst_dummy_user.mst_dummy_user_units.mst_unit',
            'mst_dummy_user.mst_dummy_user_units.mst_unit.mst_unit_i18n',
        ]);
        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_dummy_user.mst_dummy_user_i18n.name')
                    ->label('ダミーユーザー名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('score')
                    ->label('PVPスコア')
                    ->sortable(),
                UsrPartyUnitInfoColumn::make('unit_info')->label('編成キャラ情報')
                    ->getStateUsing(
                        function ($record) {
                            $mstDummyUserUnits = $record->mst_dummy_user->mst_dummy_user_units;
                            $units = [];
                            foreach ($mstDummyUserUnits as $mstDummyUserUnit) {
                                $mstUnit = $mstDummyUserUnit->mst_unit;
                                $units[] = [
                                    'id' => $mstDummyUserUnit->id,
                                    'name' => $mstUnit->mst_unit_i18n->name ?? '',
                                    'level' => $mstDummyUserUnit->level,
                                    'rank' => $mstDummyUserUnit->rank,
                                    'gradeLevel' => $mstDummyUserUnit->grade_level,
                                    'assetPath' => $mstUnit->makeAssetPath(),
                                    'bgPath' => $mstUnit->makeBgPath(),
                                ];
                            }
                            // 未設定枠も表示するためにnullで埋める
                            return array_pad($units, 10, null);
                        }),
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
                            ->label('ダミーユーザー名')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query->whereHas('mst_dummy_user.mst_dummy_user_i18n', function ($query) use ($data) {
                            $query->where('name', 'like', "%{$data['name']}%");
                        });
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
                    ->url(function ($record) {
                        return MstPvpDummyDetail::getUrl([
                            'mstPvpDummyId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
    }
}
