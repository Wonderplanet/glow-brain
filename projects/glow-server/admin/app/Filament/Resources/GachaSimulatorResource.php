<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use App\Filament\Pages\OprGachaDetail;
use App\Filament\Resources\GachaSimulatorResource\Pages;
use App\Constants\NavigationGroups;
use App\Models\Mst\OprGacha;
use App\Tables\Columns\MstIdColumn;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Filament\Pages\GachaSimulator;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Tables\Columns\PeriodStatusTableColumn;
use App\Tables\Filters\PeriodStatusTableSelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Constants\GachaType;

class GachaSimulatorResource extends Resource
{
    use Authorizable;

    protected static ?string $model = OprGacha::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static ?string $modelLabel = 'ガシャシミュレーター';

    public static function table(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                PeriodStatusTableColumn::make('period_status')->label('開催ステータス'),
                // シミュレーションやり直し必要かどうか
                TextColumn::make('simulation_required')->label('シミュレーション状態')
                    ->getStateUsing(function (OprGacha $record) {
                        if (
                            is_null($record->adm_gacha_simulation_log)
                            || !$record->adm_gacha_simulation_log->hasSimulated()
                        ) {
                            return '未実施';
                        }

                        if ($record->changed_mst_data_hash) {
                            return '要再実施';
                        }

                        return '実施済み';
                    })
                    ->color(function (OprGacha $record) {
                        if (
                            is_null($record->adm_gacha_simulation_log)
                            || !$record->adm_gacha_simulation_log->hasSimulated()
                        ) {
                            return 'secondary';
                        }

                        if ($record->changed_mst_data_hash) {
                            return 'danger';
                        }

                        return 'success';
                    })
                    ->tooltip(function (OprGacha $record) {
                        if (
                            !is_null($record->adm_gacha_simulation_log)
                            && $record->adm_gacha_simulation_log->hasSimulated()
                            && $record->changed_mst_data_hash
                        ) {
                            return 'マスタデータに変更があったため、シミュレーションの再実施が必要です';
                        }

                        return null;
                    })
                    ->icon(function (OprGacha $record) {
                        if (
                            !is_null($record->adm_gacha_simulation_log)
                            && $record->adm_gacha_simulation_log->hasSimulated()
                            && $record->changed_mst_data_hash
                        ) {
                            return 'heroicon-o-exclamation-triangle';
                        }

                        return null;
                    }),
                MstIdColumn::make('opr_gacha_info')->label('ガシャ情報')
                    ->getMstDataNameUsing(function (OprGacha $oprGacha) {
                        return $oprGacha->opr_gacha_i18n->name ?? '';
                    })
                    ->getMstDetailPageUrlUsing(function (OprGacha $oprGacha) {
                        return OprGachaDetail::getUrl([
                            'oprGachaId' => $oprGacha->id,
                        ]);
                    }),
                TextColumn::make('gacha_type_label')->label('ガシャタイプ')->sortable(
                    query: fn(Builder $query) => $query->orderBy('gacha_type'),
                ),
                TextColumn::make('start_at')->label('開始日時')->sortable(),
                TextColumn::make('end_at')->label('終了日時')->sortable(),
            ])
            ->filters([
                Filter::make('opr_gacha_id')->label('ガシャID')
                    ->form([
                        TextInput::make('opr_gacha_id')->label('ガシャID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['opr_gacha_id'])) {
                            return $query;
                        }

                        return $query->where('id', 'like', "%{$data['opr_gacha_id']}%");
                    }),
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
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action->label('適用'),
            )
            ->actions([
                Action::make('opr_gacha_detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (OprGacha $record) {
                        return GachaSimulator::getUrl([
                            'oprGachaId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([])
            ->emptyStateActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGachaSimulators::route('/'),
        ];
    }
}
