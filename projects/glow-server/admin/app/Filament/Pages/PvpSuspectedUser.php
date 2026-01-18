<?php

namespace App\Filament\Pages;

use App\Constants\ContentType;
use App\Filament\Resources\PvpSuspectedUsersResource;
use App\Models\Usr\SysPvpSeason;
use App\Models\Usr\UsrPvp;
use App\Services\PvpCacheService;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PvpSuspectedUser extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.pvp-suspected-user';
    protected static ?string $title = 'ランクマッチ不正疑惑操作';
    protected static bool $shouldRegisterNavigation = false;

    public string $userIds = '';
    public string $userId = '';
    public string $sysPvpSeasonId = '';
    public bool $aggregationFlg = false;

    protected $queryString = [
        'userId',
        'userIds',
        'sysPvpSeasonId',
    ];

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            PvpSuspectedUsersResource::getUrl() => 'ランクマッチ不正疑惑操作',
            PvpSuspectedUserList::getUrl(['sysPvpSeasonId' => $this->sysPvpSeasonId]) => 'ランクマッチ不正疑惑ユーザー一覧',
            PvpSuspectedUser::getUrl(['userIds' => $this->userIds, 'sysPvpSeasonId' => $this->sysPvpSeasonId]) => '一括復帰',
        ]);

        $sysPvpSeason = SysPvpSeason::query()
            ->where('id',$this->sysPvpSeasonId)
            ->first();

        $now = CarbonImmutable::now();
        if (!is_null($sysPvpSeason->closed_at)) {
            $closedDate = (new CarbonImmutable($sysPvpSeason->closed_at));
            if ($closedDate < $now) {
                $this->aggregationFlg = true;
            }
        }
    }

    public function table(Table $table): Table
    {
        $userIds = explode(',', $this->userIds);
        $query = UsrPvp::query()
            ->whereIn('usr_user_id', $userIds)
            ->where('sys_pvp_season_id', $this->sysPvpSeasonId)
            ->with(
                'usr_user',
                'usr_user.usr_user_profiles'
            );

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user.usr_user_profiles.my_id')
                    ->label('MY_ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user.usr_user_profiles.name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('usr_user_id')
                    ->form([
                        TextInput::make('usr_user_id')
                            ->label('ユーザーID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['usr_user_id'])) {
                            return $query;
                        }
                        return $query->where('usr_user_id', 'like', "%{$data['usr_user_id']}%");
                    }),
                Filter::make('my_id')
                    ->form([
                        TextInput::make('my_id')
                            ->label('MY_ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['my_id'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('usr_user.usr_user_profiles', function ($query) use ($data) {
                                $query->where('my_id', 'like', "%{$data['my_id']}%");
                        });
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )->actions([
                Action::make('detail')
                ->label('詳細')
                ->button()
                ->url(function ($record) {
                    return SuspectedUserDetail::getUrl([
                        'userId' => $record->usr_user_id,
                        'contentType' => ContentType::PVP,
                        'targetId' => $this->sysPvpSeasonId,
                    ]);
                })->openUrlInNewTab(),
            ]);
    }

    /**
     * ランクマッチランキング一括復帰
     *
     * ランクマッチにおいて不正疑惑がありランキングから除外されたユーザーの一括復帰処理
     */
    public function reactivateUserInRanking()
    {
        $userIds = explode(',', $this->userIds);
        $now = CarbonImmutable::now();

        $sysPvpSeason = SysPvpSeason::query()
            ->where('id',$this->sysPvpSeasonId)
            ->first();

        if (!is_null($sysPvpSeason->closed_at)) {
            $closedDate = (new CarbonImmutable($sysPvpSeason->closed_at));
            if ($now > $closedDate) {
                Notification::make()
                    ->title('集計期間が終了しているため,復帰できません。')
                    ->danger()
                    ->send();

                $this->redirect(
                    PvpSuspectedUserList::getUrl(['sysPvpSeasonId' => $this->sysPvpSeasonId]),
                );
                return;
            }
        }

        $usrPvps = UsrPvp::query()
            ->where('sys_pvp_season_id', $this->sysPvpSeasonId)
            ->whereIn('usr_user_id', $userIds)
            ->get()
            ->keyBy('usr_user_id');

        $scoreMap = [];
        foreach ($usrPvps as $usrPvp) {
            $scoreMap[$usrPvp->usr_user_id] = $usrPvp->score;
        }

        /** @var PvpCacheService $pvpCacheService */
        $pvpCacheService = app(PvpCacheService::class);
        // ランキングのスコアを-1からusr_pvps.max_scoreの値に戻す
        $pvpCacheService->addRankingScoreAll($this->sysPvpSeasonId, $scoreMap);

        UsrPvp::query()
            ->where('sys_pvp_season_id', $this->sysPvpSeasonId)
            ->whereIn('usr_user_id', $userIds)
            ->update(['is_excluded_ranking' => false]);

        Notification::make()
            ->title('一括復帰が完了しました')
            ->success()
            ->send();

        $this->redirect(
            PvpSuspectedUserList::getUrl(['sysPvpSeasonId' => $this->sysPvpSeasonId]),
        );
    }
}
