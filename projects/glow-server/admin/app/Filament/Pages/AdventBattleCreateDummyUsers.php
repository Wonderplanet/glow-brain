<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Constants\Database;
use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Services\AdventBattleCacheService as baseAdventBattleCacheService;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Entities\AdventBattleRankingEntity;
use App\Filament\Authorizable;
use App\Filament\Resources\AdventBattleRankingResource;
use App\Models\Mst\MstAdventBattle;
use App\Models\Mst\MstUnit;
use App\Models\Usr\UsrAdventBattle;
use App\Models\Usr\UsrUnit;
use App\Models\Usr\UsrUser;
use App\Models\Usr\UsrUserProfile;
use App\Services\AdventBattleCacheService;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\LogUserPartyTrait;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdventBattleCreateDummyUsers extends Page
{
    use Authorizable;
    use DatabaseTransactionTrait;
    use LogUserPartyTrait;
    
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static string $view = 'filament.pages.advent-battle-create-dummy-users';
    protected static ?string $title = '降臨バトルダミーUSER生成';
    protected static bool $shouldRegisterNavigation = true;

    public string $mstAdventBattleId = '';
    public string $createUserCount = '';
    public string $adventBattlePoint = '';
    public ?array $rankingData = [];

    protected $queryString = [
        'mstAdventBattleId',
    ];

    private baseAdventBattleCacheService $baseAdventBattleCacheService;
    private AdventBattleCacheService $adventBattleCacheService;
    private MstConfigService $mstConfigService;

    public function __construct()
    {
        $this->baseAdventBattleCacheService = app(baseAdventBattleCacheService::class);
        $this->adventBattleCacheService = app(AdventBattleCacheService::class);
        $this->mstConfigService = app(MstConfigService::class);
    }

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            AdventBattleRankingResource::getUrl() => '降臨バトル',
            self::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]) => '降臨バトルダミーUSER生成',
        ]);
    }

    public function Form(Form $form): Form
    {
        $now = CarbonImmutable::now();
        
        // 有効な降臨バトルIDを取得して分類
        $adventBattles = MstAdventBattle::with('mst_advent_battle_i18n')
            ->orderBy('start_at', 'desc')
            ->get();

        $activeOptions = [];
        $upcomingOptions = [];
        $pastOptions = [];

        foreach ($adventBattles as $adventBattle) {
            $name = $adventBattle->mst_advent_battle_i18n->name ?? $adventBattle->id;
            $label = $adventBattle->id . ' - ' . $name;
            $startAt = $adventBattle->start_at;
            $endAt = $adventBattle->end_at;

            if ($now->between($startAt, $endAt)) {
                // 開催中
                $activeOptions[$adventBattle->id] = '[開催中] ' . $label;
            } elseif ($now < $startAt) {
                // 開催予定
                $upcomingOptions[$adventBattle->id] = '[開催予定] ' . $label;
            } else {
                // 終了済み
                $pastOptions[$adventBattle->id] = '[終了済み] ' . $label;
            }
        }

        // オプションを結合（開催中 → 開催予定 → 終了済みの順）
        $adventBattleOptions = array_merge($activeOptions, $upcomingOptions, $pastOptions);

        return $form
            ->schema([
                Select::make('mstAdventBattleId')
                    ->label('降臨バトルID')
                    ->required()
                    ->options($adventBattleOptions)
                    ->searchable()
                    ->placeholder('降臨バトルを選択してください'),
                TextInput::make('createUserCount')
                    ->label('作成するユーザー数')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(50000)
                    ->placeholder('作成するユーザー数を入力'),
                TextInput::make('adventBattlePoint')
                    ->label('降臨バトルポイント')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('降臨バトルポイントを入力'),
                Actions::make([
                    Action::make('createDummyUsers')
                        ->label('ダミーユーザーを生成してランキング更新')
                        ->action(fn () => $this->createDummyUsers()),
                    Action::make('ranking')
                        ->label('ランキング表示')
                        ->action(function () {
                            $this->rankingData = $this->ranking()->toArray();
                        }),
                ])
            ]);
    }

    public function createDummyUsers() {
        $mstAdventBattleId = $this->mstAdventBattleId;
        $createUserCount = (int)$this->createUserCount;
        $adventBattlePoint = (int)$this->adventBattlePoint;

        if (!$mstAdventBattleId) {
            Notification::make()
                ->title('降臨バトルIDが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        if ($createUserCount <= 0) {
            Notification::make()
                ->title('作成するユーザー数が正しくありません。')
                ->danger()
                ->send();
            return;
        }

        if ($adventBattlePoint < 0) {
            Notification::make()
                ->title('降臨バトルポイントが正しくありません。')
                ->danger()
                ->send();
            return;
        }

        try {
            $this->transaction(function () use ($mstAdventBattleId, $createUserCount, $adventBattlePoint) {
                // MstUnitから有効な一件を取得
                $defaultMstUnit = MstUnit::first();
                if (!$defaultMstUnit) {
                    throw new \RuntimeException('有効なMstUnitが見つかりません');
                }
                $defaultMstUnitId = $defaultMstUnit->id;
                $defaultMstEmblemId = ''; // デフォルトのエンブレムID
                
                $userData = [];
                $usrUsers = [];
                $usrUserProfiles = [];
                $usrAdventBattles = [];
                $usrUnits = [];
                $now = CarbonImmutable::now();
                $chunkSize = 1000; // 1000件ずつ処理

                // 今回の処理を識別するためのランダムなID
                $randomCreateId = Str::random(8);
                
                for ($i = 1; $i <= $createUserCount; $i++) {
                    // ダミーユーザーID生成
                    $dummyUserId = 'dummy_advent_' . $mstAdventBattleId . '_' . Str::random(8) . '_' . $i;
                    $profileId = Str::uuid();
                    $adventBattleId = Str::uuid();
                    $usrUnitId = Str::uuid();
                    $myId = 'DUMMY' . $mstAdventBattleId . '_' . $randomCreateId . '_' . str_pad($i, 6, '0', STR_PAD_LEFT);
                    
                    // usr_usersデータを準備
                    $usrUsers[] = [
                        'id' => $dummyUserId,
                        'status' => 0, // NORMAL
                        'tutorial_status' => '',
                        'tos_version' => 0,
                        'privacy_policy_version' => 0,
                        'global_consent_version' => 0,
                        'bn_user_id' => '',
                        'is_account_linking_restricted' => 0,
                        'client_uuid' => '',
                        'suspend_end_at' => null,
                        'game_start_at' => $now->toDateTimeString(),
                        'created_at' => $now->toDateTimeString(),
                        'updated_at' => $now->toDateTimeString(),
                    ];

                    // usr_user_profilesデータを準備
                    $usrUserProfiles[] = [
                        'id' => $profileId,
                        'usr_user_id' => $dummyUserId,
                        'my_id' => $myId,
                        'name' => 'ダミーユーザー' . $i,
                        'is_change_name' => 0,
                        'birth_date' => '',
                        'mst_unit_id' => $defaultMstUnitId,
                        'mst_emblem_id' => $defaultMstEmblemId,
                        'name_update_at' => null,
                        'created_at' => $now->toDateTimeString(),
                        'updated_at' => $now->toDateTimeString(),
                    ];

                    // usr_unitsデータを準備（初期ユニット）
                    $usrUnits[] = [
                        'id' => $usrUnitId,
                        'usr_user_id' => $dummyUserId,
                        'mst_unit_id' => $defaultMstUnitId,
                        'level' => 1,
                        'rank' => 0,
                        'grade_level' => 1,
                        'battle_count' => 0,
                        'is_new_encyclopedia' => 1,
                        'created_at' => $now->toDateTimeString(),
                        'updated_at' => $now->toDateTimeString(),
                    ];

                    // パーティ情報を作成（max_score_party用）
                    $maxScoreParty = [
                        [
                            'mst_unit_id' => $defaultMstUnitId,
                            'level' => 1,
                            'rank' => 0,
                            'grade_level' => 1,
                        ]
                    ];

                    // usr_advent_battlesデータを準備
                    $usrAdventBattles[] = [
                        'id' => $adventBattleId,
                        'usr_user_id' => $dummyUserId,
                        'mst_advent_battle_id' => $mstAdventBattleId,
                        'max_score' => $adventBattlePoint,
                        'total_score' => $adventBattlePoint,
                        'challenge_count' => 1,
                        'reset_challenge_count' => 1,
                        'reset_ad_challenge_count' => 0,
                        'clear_count' => 1,
                        'max_received_max_score_reward' => 0,
                        'received_rank_reward_group_id' => '',
                        'received_raid_reward_group_id' => '',
                        'is_ranking_reward_received' => false,
                        'is_excluded_ranking' => false,
                        'latest_reset_at' => null,
                        'max_score_party' => json_encode($maxScoreParty),
                        'created_at' => $now->toDateTimeString(),
                        'updated_at' => $now->toDateTimeString(),
                    ];

                    // ランキング用のデータを準備
                    $userData[$dummyUserId] = $adventBattlePoint;

                    // チャンクサイズに達したら処理実行
                    if (count($usrUsers) >= $chunkSize || $i == $createUserCount) {
                        // バッチでデータを挿入
                        UsrUser::insert($usrUsers);

                        UsrUserProfile::insert($usrUserProfiles);

                        // usr_unitsテーブルに挿入
                        UsrUnit::insert($usrUnits);

                        UsrAdventBattle::insert($usrAdventBattles);

                        // 配列をリセット
                        $usrUsers = [];
                        $usrUserProfiles = [];
                        $usrAdventBattles = [];
                        $usrUnits = [];
                    }
                }

                // 追加したダミーユーザーの情報のみをランキングキャッシュに追加
                if (!empty($userData)) {
                    $this->adventBattleCacheService->addRankingScoreAll($mstAdventBattleId, $userData);
                }
            }, [Database::TIDB_CONNECTION]);

            Notification::make()
                ->title($createUserCount . '人のダミーユーザーを作成し、ランキングを更新しました。')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('降臨バトルID:' . $mstAdventBattleId . ' ダミーユーザー作成エラー', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            Notification::make()
                ->title('ダミーユーザーの作成に失敗しました。')
                ->body('エラー: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        $this->redirect(
            self::getUrl([
                'mstAdventBattleId' => $mstAdventBattleId,
            ]),
        );
    }

    public function ranking() {
        $mstAdventBattleId = $this->mstAdventBattleId;

        if (!$mstAdventBattleId) {
            return collect();
        }

        $usrUserIdScoreMap = $this->baseAdventBattleCacheService->getTopRankedPlayerScoreMap($mstAdventBattleId);

        //取得したユーザーのusr_profilesを取得
        $usrUserIds = collect(array_keys($usrUserIdScoreMap));

        $usrUserProfiles = UsrUserProfile::whereIn('usr_user_id', $usrUserIds)
            ->get()
            ->keyBy('usr_user_id'); // プロパティ名で直接keyByを使用

        $usrAdventBattles = UsrAdventBattle::where('mst_advent_battle_id', $mstAdventBattleId)
            ->whereIn('usr_user_id', $usrUserIds)
            ->get()
            ->keyBy('usr_user_id'); // プロパティ名で直接keyByを使用

        $adventBattleRankingEntityList = $this->generateAdventBattleRankingEntityList(
            $usrUserIdScoreMap,
            $usrUserProfiles,
            $usrAdventBattles
        );

        $ranking = $adventBattleRankingEntityList
            ->map(fn (AdventBattleRankingEntity $rankingEntity) => $rankingEntity->formatToResponse());

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            AdventBattleRankingResource::getUrl() => '降臨バトル',
            self::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]) => '降臨バトルダミーUSER生成',
        ]);

        return $ranking;
    }

    /**
     * ランキングデータリストを生成
     * @param array<string, float> $usrUserIdScoreMap usr_user_id => score
     * @param Collection<\App\Domain\User\Eloquent\Models\UsrUserProfileInterface> $usrUserProfiles
     * @param Collection<\App\Domain\AdventBattle\Models\UsrAdventBattle> $usrAdventBattles
     * @return Collection<AdventBattleRankingEntity>
     */
    private function generateAdventBattleRankingEntityList(
        array $usrUserIdScoreMap,
        Collection $usrUserProfiles,
        Collection $usrAdventBattles
    ): Collection {
        $adventBattleRankingEntityList = collect();
        $rank = 0;
        $prevScore = 0;
        $sameScoreCount = 1;

        // getMaxScorePartyArray()取得したunitsの中からmst_unit_idを全て取得
        $mstUnitIds = $usrAdventBattles->flatMap(
            function (UsrAdventBattle $usrAdventBattle) {
                $maxScoreParty = $usrAdventBattle->getMaxScorePartyArray();
                return collect($maxScoreParty ?? [])->pluck('mst_unit_id');
            }
        )->unique();

        $mstUnits = MstUnit::whereIn('id', $mstUnitIds)
            ->get()
            ->keyBy(fn(MstUnit $mstUnit) => $mstUnit->id);

        
        foreach ($usrUserIdScoreMap as $rankerUsrUserId => $score) {
            // floatになっているのでint型に変換
            $score = (int)$score;

            if ($score !== $prevScore) {
                // 同率スコアのユーザー数分順位を進める
                $rank += $sameScoreCount;
                $sameScoreCount = 1;
            } else {
                $sameScoreCount++;
            }
            /** @var \App\Domain\User\Eloquent\Models\UsrUserProfileInterface $usrUserProfile */
            $usrUserProfile = $usrUserProfiles->get($rankerUsrUserId);

            /** @var UsrAdventBattle $usrAdventBattle */
            $usrAdventBattle = $usrAdventBattles->get($rankerUsrUserId);
            $maxScoreParty = $usrAdventBattle?->getMaxScorePartyArray() ?? [];
            $partyStatus = $this->partyStatus($maxScoreParty, $mstUnits);

            $adventBattleRankingEntity = new AdventBattleRankingEntity(
                $rank,
                $rankerUsrUserId,
                $usrUserProfile?->getName() ?? '',
                $score,
                $partyStatus,
            );

            $adventBattleRankingEntityList->push($adventBattleRankingEntity);
            $prevScore = $score;
        }
        return $adventBattleRankingEntityList;
    }

}
