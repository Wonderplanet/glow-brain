<?php

namespace App\Filament\Pages;

use App\Constants\GachaType;
use App\Constants\UserSearchTabs;
use App\Entities\Clock;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\OprGacha;
use App\Models\Usr\UsrGacha;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserGacha extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-gacha';

    public string $currentTab = UserSearchTabs::GACHA->value;

    public function mount()
    {
        parent::mount();

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = UsrGacha::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                ColumnGroup::make('ガシャ情報', [
                    TextColumn::make('opr_gacha.id')->label('ガシャID'),
                    TextColumn::make('opr_gacha.opr_gacha_i18n.name')->label('ガシャ名'),
                    TextColumn::make('opr_gacha.upper_group')->label('天井グループ'),
                ]),
                ColumnGroup::make('通常', [
                    $this->makePlayCountLimitTextColumn(
                        usrGachaCountColumnName: 'count',
                        label: '通算回数/上限',
                    ),
                    $this->makePlayCountLimitTextColumn(
                        usrGachaCountColumnName: 'daily_count',
                        label: '日次で引いた回数/上限',
                    ),
                    TextColumn::make('played_at')->label('最後に引いた時間')
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
                ColumnGroup::make('広告で引く', [
                    $this->makePlayCountLimitTextColumn(
                        usrGachaCountColumnName: 'ad_count',
                        label: '通算回数/上限',
                    ),
                    $this->makePlayCountLimitTextColumn(
                        usrGachaCountColumnName: 'ad_daily_count',
                        label: '日次で引いた回数/上限',
                    ),
                    TextColumn::make('ad_played_at')->label('最後に引いた時間')
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->filters([
                Filter::make('opr_gacha_id')
                    ->form([
                        TextInput::make('opr_gacha_id')->label('ガシャID')
                    ])
                    ->label('ガシャID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['opr_gacha_id'])) {
                            return $query;
                        }
                        return $query->where('opr_gacha_id', $data['opr_gacha_id']);
                    }),
                Filter::make('opr_gacha_name')
                    ->form([
                        TextInput::make('opr_gacha_name')->label('ガシャ名')
                    ])
                    ->label('ガシャ名')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['opr_gacha_name'])) {
                            return $query;
                        }

                        $oprGachaIds = OprGacha::query()
                            ->whereHas('opr_gacha_i18n', function (Builder $query) use ($data) {
                                return $query->where('name', 'like', "%{$data['opr_gacha_name']}%");
                            })
                            ->pluck('id');

                        return $query->whereIn('opr_gacha_id', $oprGachaIds);
                    }),
                SelectFilter::make('upper_group')
                    ->options(function () {
                        return OprGacha::query()
                            ->distinct('upper_group')
                            ->get()
                            ->mapWithKeys(function (OprGacha $oprGacha) {
                                return [$oprGacha->upper_group => $oprGacha->upper_group];
                            });
                    })
                    ->searchable()
                    ->label('天井グループ')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where(
                            'opr_gacha_id',
                            OprGacha::query()
                                ->where('upper_group', $data['value'])
                                ->pluck('id')
                        );
                    }),
                ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('edit')->label('編集')
                    ->button()
                    ->url(function (UsrGacha $usrGacha) {
                        return EditUserGacha::getUrl([
                            'userId' => $this->userId,
                            'oprGachaId' => $usrGacha->opr_gacha_id,
                        ]);
                    })
                    ->visible(fn () => EditUserGacha::canAccess()),
            ], position: ActionsPosition::BeforeColumns);
    }

    private function makePlayCountLimitTextColumn(
        string $usrGachaCountColumnName,
        string $label,
    ): ?TextColumn {
        /** @var Clock $clock */
        $clock = app(Clock::class);

        // 指定列ごとに、リセット処理を変更する
        switch ($usrGachaCountColumnName) {
            case 'count':
                $oprGachaLimitColumnName = 'total_play_limit_count';
                $resetPlayCountCallback = function (UsrGacha $usrGacha) {
                    return $usrGacha;
                };
                break;
            case 'daily_count':
                $oprGachaLimitColumnName = 'daily_play_limit_count';
                $resetPlayCountCallback = function (UsrGacha $usrGacha) use ($clock) {
                    if ($clock->isFirstToday($usrGacha->played_at ?? '')) {
                        $usrGacha->resetDailyCount();
                    }
                    return $usrGacha;
                };
                break;
            case 'ad_count':
                $oprGachaLimitColumnName = 'total_ad_limit_count';
                $resetPlayCountCallback = function (UsrGacha $usrGacha) {
                    return $usrGacha;
                };
                break;
            case 'ad_daily_count':
                $oprGachaLimitColumnName = 'daily_ad_limit_count';
                $resetPlayCountCallback = function (UsrGacha $usrGacha) use ($clock) {
                    if ($clock->isFirstToday($usrGacha->ad_played_at ?? '')) {
                        $usrGacha->resetAdDailyCount();
                    }
                    return $usrGacha;
                };
                break;
            default:
                return null;
        }

        return TextColumn::make($usrGachaCountColumnName)->label($label)
            ->sortable()
            ->state($resetPlayCountCallback)
            ->formatStateUsing(function (UsrGacha $usrGacha) use (
                $oprGachaLimitColumnName,
                $usrGachaCountColumnName,
                $resetPlayCountCallback,
            ) {
                // ステップアップガシャの場合、日次と広告は表示しない
                $isStepup = $usrGacha->opr_gacha?->toEntity()->isStepup() ?? false;
                if ($isStepup && in_array($usrGachaCountColumnName, ['ad_count', 'ad_daily_count'])) {
                    return '-';
                }

                $usrGacha = $resetPlayCountCallback($usrGacha);

                // ステップアップガシャの通算回数の場合、上限を計算
                if ($isStepup) {
                    $stepupGacha = $usrGacha->opr_stepup_gacha;
                    if ($stepupGacha && !is_null($stepupGacha->max_loop_count)) {
                        $limit = $stepupGacha->max_step_number * $stepupGacha->max_loop_count;
                    } else {
                        $limit = null;
                    }
                } else {
                    $limit = $usrGacha->opr_gacha?->$oprGachaLimitColumnName;
                }

                if (is_null($limit)) {
                    $limit = '∞';
                }
                return sprintf(
                    '%d / %s',
                    $usrGacha->$usrGachaCountColumnName,
                    (string) $limit,
                );
            })
            ->color(function (UsrGacha $usrGacha, $state) use (
                $oprGachaLimitColumnName,
                $usrGachaCountColumnName,
                $resetPlayCountCallback,
            ) {
                // ステップアップガシャの場合、色は付けない
                $isStepup = $usrGacha->opr_gacha?->toEntity()->isStepup() ?? false;
                if ($isStepup && in_array($usrGachaCountColumnName, ['ad_count', 'ad_daily_count'])) {
                    return null;
                }

                $usrGacha = $resetPlayCountCallback($usrGacha);

                // ステップアップガシャの通算回数の場合、上限チェック
                if ($isStepup) {
                    $stepupGacha = $usrGacha->opr_stepup_gacha;
                    if ($stepupGacha && !is_null($stepupGacha->max_loop_count)) {
                        $limit = $stepupGacha->max_step_number * $stepupGacha->max_loop_count;
                    } else {
                        $limit = null;
                    }
                } else {
                    $limit = $usrGacha->opr_gacha?->$oprGachaLimitColumnName;
                }

                if (is_null($limit)) {
                    return null;
                }
                return $usrGacha->$usrGachaCountColumnName >= $limit ? 'danger' : null;
            });
    }
}
