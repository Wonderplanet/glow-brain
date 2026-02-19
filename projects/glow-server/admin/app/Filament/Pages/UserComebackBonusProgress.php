<?php

namespace App\Filament\Pages;

use App\Constants\Database;
use App\Constants\NavigationGroups;
use App\Constants\UserSearchTabs;
use App\Domain\Common\Services\ClockService;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstComebackBonus;
use App\Models\Mst\MstComebackBonusSchedule;
use App\Models\Usr\UsrComebackBonusProgress;
use App\Tables\Columns\MstIdColumn;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\RewardInfoGetTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action as FilamentAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserComebackBonusProgress extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use DatabaseTransactionTrait;
    use RewardInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static string $view = 'filament.pages.user-comeback-bonus-progress';
    public string $currentTab = UserSearchTabs::COMEBACK_BONUS->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = UsrComebackBonusProgress::query()
            ->where('usr_user_id', $this->userId)
            ->orderBy('created_at', 'desc')
            ->with([
                'mst_comeback_bonus_schedule',
            ]);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                MstIdColumn::make('schedule_info')
                    ->label('マスター情報')
                    ->getMstUsing(function (UsrComebackBonusProgress $model) {
                        return $model->mst_comeback_bonus_schedule;
                    })
                    ->getMstDetailPageUrlUsing(function (UsrComebackBonusProgress $model) {
                        return MstComebackBonusDetail::getUrl([
                            'mstComebackBonusScheduleId' => $model->mst_comeback_bonus_schedule_id,
                        ]);
                    }),
                TextColumn::make('progress')
                    ->label('受取日数')
                    ->suffix('日'),
                TextColumn::make('start_count')
                    ->label('開始回数')
                    ->suffix('回'),
                TextColumn::make('start_at')
                    ->label('期間開始日時'),
                TextColumn::make('end_at')
                    ->label('期間終了日時'),
            ])
            ->filters([
                Filter::make('id')
                    ->form([
                        TextInput::make('id')
                            ->label('ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('schedule_id')
                    ->form([
                        TextInput::make('schedule_id')
                            ->label('スケジュールID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['schedule_id'])) {
                            return $query;
                        }
                        return $query->where('mst_comeback_bonus_schedule_id', 'like', "%{$data['schedule_id']}%");
                    }),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('reset')
                    ->label('リセット')
                    ->requiresConfirmation()
                    ->action(function (UsrComebackBonusProgress $record) {
                        $this->transaction(function () use ($record) {
                            $now = CarbonImmutable::now();
                            $durationDays = $record->mst_comeback_bonus_schedule->duration_days;
                            $clockService = app()->make(ClockService::class);
                            $receiveTerm = $clockService->calcDaysRange($now, $durationDays);
                            $record->resetProgress($now);
                            $record->resetTerm($receiveTerm->startAt, $receiveTerm->endAt);
                            $record->save();
                        });
                        $this->redirectRoute('filament.admin.pages.user-comeback-bonus-progress', ['userId' => $this->userId]);
                    })
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ]);
    }
}
