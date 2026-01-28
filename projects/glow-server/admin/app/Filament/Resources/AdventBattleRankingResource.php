<?php

namespace App\Filament\Resources;

use App\Constants\AdventBattleType;
use App\Constants\NavigationGroups;
use App\Constants\PeriodStatus;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Filament\Authorizable;
use App\Filament\Pages\AdventBattleRankingRegenerations;
use App\Filament\Resources\AdventBattleRankingResource\Pages;
use App\Models\Mst\MstAdventBattle;
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
use Illuminate\Database\Eloquent\Model;

class AdventBattleRankingResource extends Resource
{
    use Authorizable;

    protected static ?string $model = MstAdventBattle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static ?string $modelLabel = '降臨バトル';

    private MstConfigService $mstConfigService;

    public function __construct()
    {
        $this->mstConfigService = app(MstConfigService::class);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                PeriodStatusTableColumn::make('period_status')
                    ->label('開催ステータス'),
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('advent_battle_type')
                    ->label('降臨バトルタイプ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_at')
                    ->label('開始日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('終了日')
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
                PeriodStatusTableSelectFilter::make('period_status')
                    ->label('開催ステータス')
                    ->default(PeriodStatus::DURING->value),
                SelectFilter::make('advent_battle_type')
                    ->options(AdventBattleType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('advent_battle_type', $data);
                    })
                    ->label('降臨バトルタイプ'),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('detail')
                ->label('ランキング再生成ページへ')
                ->button()
                ->url(function (Model $record) {
                    return AdventBattleRankingRegenerations::getUrl([
                        'mstAdventBattleId' => $record->id,
                    ]);
                }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdventBattleRankings::route('/'),
        ];
    }
}
