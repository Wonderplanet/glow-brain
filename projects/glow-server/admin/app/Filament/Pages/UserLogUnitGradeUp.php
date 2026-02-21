<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Constants\UserSearchTabs;
use App\Models\Log\LogUnitGradeUp;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Models\Mst\MstUnit;
use App\Traits\UserResourceLogUnitTrait;
use App\Traits\RewardInfoGetTrait;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class UserLogUnitGradeUp extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;
    use UserResourceLogUnitTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-unit-grade-up';

    public string $currentTab = UserSearchTabs::LOG_UNIT_GRADE_UP->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {

        $query = LogUnitGradeUp::query()
            ->where('usr_user_id', $this->userId);

        $rewardDtoList = MstUnit::query()->get()->map(function (MstUnit $mstUnit) {
            return $mstUnit->reward;
        });

        $rewardInfos = $this->getRewardInfos($rewardDtoList);

        $columns = $this->getResourceLogUnitUpColumns($rewardInfos);
        $addColumn = [
            TextColumn::make('before_grade_level')
                ->label('強化前のグレード')
                ->searchable()
                ->sortable(),
            TextColumn::make('after_grade_level')
                ->label('強化後のグレード')
                ->searchable()
                ->sortable(),
            TextColumn::make('created_at')
                ->label('グレードアップ日時')
                ->searchable()
                ->sortable()
            ];
        $columns = array_merge($columns, $addColumn);

        return $table
            ->query($query)
            ->columns(
                $columns
            )
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        Filter::make('mst_unit_id')
                            ->form([
                                TextInput::make('mst_unit_id')
                                    ->label('キャラ情報')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['mst_unit_id'])) {
                                    return $query;
                                }
                                return $query->where('mst_unit_id', 'like', "%{$data['mst_unit_id']}%");
                            }),
                    ]
                ),
                FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_unit_grade_up')
            ]);
    }
}

