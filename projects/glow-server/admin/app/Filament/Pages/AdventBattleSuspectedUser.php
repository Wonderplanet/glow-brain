<?php

namespace App\Filament\Pages;

use App\Constants\ContentType;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Filament\Resources\AdventBattleSuspectedUsersResource;
use App\Models\Log\LogSuspectedUser;
use App\Models\Mst\MstAdventBattle;
use App\Models\Usr\UsrAdventBattle;
use App\Services\AdventBattleCacheService;
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
use Illuminate\Support\Facades\DB;

class AdventBattleSuspectedUser extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.advent-battle-suspected-user';
    protected static ?string $title = '降臨バトル不正疑惑操作';
    protected static bool $shouldRegisterNavigation = false;

    private MstConfigService $mstConfigService;
    public function __construct()
    {
        $this->mstConfigService = app(MstConfigService::class);
    }

    public string $userIds = '';
    public string $userId = '';
    public string $mstAdventBattleId = '';
    public bool $aggregationFlg = false;

    protected $queryString = [
        'userId',
        'userIds',
        'mstAdventBattleId',
    ];

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            AdventBattleSuspectedUsersResource::getUrl() => '降臨バトル不正疑惑操作',
            AdventBattleSuspectedUserList::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]) => '降臨バトル不正疑惑ユーザー一覧',
            AdventBattleSuspectedUser::getUrl(['userIds' => $this->userIds, 'mstAdventBattleId' => $this->mstAdventBattleId]) => '一括復帰',
        ]);

        $mstAdventBattle = MstAdventBattle::query()
            ->where('id',$this->mstAdventBattleId)
            ->first();
        $now = CarbonImmutable::now();
        $aggregateHours = $this->mstConfigService->getAdventBattleRankingAggregateHours();
        $endDate = (new CarbonImmutable($mstAdventBattle->end_at))->addHours($aggregateHours);
        if ($now > $endDate) {
            $this->aggregationFlg = true;
        }
    }

    public function table(Table $table): Table
    {
        $userIds = explode(',', $this->userIds);
        $query = LogSuspectedUser::query()
            ->whereIn('usr_user_id', $userIds)
            ->whereIn('id', function($query) use ($userIds) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('log_suspected_users')
                      ->whereIn('usr_user_id', $userIds)
                      ->groupBy('usr_user_id');
            })
            ->with(
                'usr_user',
                'usr_user_profile'
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
                TextColumn::make('usr_user_profile.my_id')
                    ->label('MY_ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profile.name')
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
                            ->whereHas('usr_user_profile', function ($query) use ($data) {
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
                        'contentType' => ContentType::ADVENT_BATTLE,
                        'targetId' => $this->mstAdventBattleId,
                    ]);
                })->openUrlInNewTab(),
            ]);
    }

    /**
     * 降臨バトルランキング　一括復帰
     *
     * 降臨バトルにおいて不正疑惑がありランキングから除外されたユーザーの
     * 一括復帰処理
     */
    public function reactivateUserInRanking()
    {
        $userIds = explode(',', $this->userIds);
        $mstAdventBattleId = $this->mstAdventBattleId;
        $now = CarbonImmutable::now();

        $mstAdventBattle = MstAdventBattle::query()
            ->where('id',$mstAdventBattleId)
            ->first();

        $aggregateHours = $this->mstConfigService->getAdventBattleRankingAggregateHours();
        $endDate = (new CarbonImmutable($mstAdventBattle->end_at))->addHours($aggregateHours);
        if ($now > $endDate) {
            Notification::make()
            ->title('集計期間が終了しているため,復帰できません。')
            ->danger()
            ->send();

            $this->redirect(
                AdventBattleSuspectedUserList::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]),
            );
            return;
        }

        $usrAdventBattles = UsrAdventBattle::query()
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->whereIn('usr_user_id', $userIds)
            ->get()
            ->keyBy('usr_user_id')
            ->toArray();

        $usrAdventBattleUpdateData = [];
        $scoreMap = [];
        foreach ($userIds as $usrUserId) {
            $scoreMap[$usrUserId] = $usrAdventBattles[$usrUserId]['max_score'];
            $usrAdventBattleUpdateData[] = [
                'id' => $usrAdventBattles[$usrUserId]['id'],
                'mst_advent_battle_id' => $mstAdventBattleId,
                'usr_user_id' => $usrUserId,
                'is_excluded_ranking' => false
            ];
        }

        /** @var AdventBattleCacheService $adventBattleCacheService */
        $adventBattleCacheService = app(AdventBattleCacheService::class);
        // ランキングのスコアを-1からusr_advent_battles.max_scoreの値に戻す
        $adventBattleCacheService->addRankingScoreAll($mstAdventBattleId, $scoreMap);

        UsrAdventBattle::upsert($usrAdventBattleUpdateData, ['id','usr_user_id','mst_advent_battle_id'], ['is_excluded_ranking']);

        Notification::make()
            ->title('一括復帰が完了しました')
            ->success()
            ->send();

        $this->redirect(
            AdventBattleSuspectedUserList::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]),
        );
    }
}
