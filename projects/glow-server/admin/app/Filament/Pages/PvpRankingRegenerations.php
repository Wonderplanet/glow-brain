<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Entities\PvpRankingEntity;
use App\Filament\Authorizable;
use App\Filament\Resources\PvpRankingResource;
use App\Models\Mst\MstPvp;
use App\Models\Usr\SysPvpSeason;
use App\Models\Usr\UsrPvp;
use App\Models\Usr\UsrUserProfile;
use App\Services\PvpCacheService;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PvpRankingRegenerations extends Page
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static string $view = 'filament.pages.pvp-ranking-regenerations';
    protected static ?string $title = 'ランクマッチランキング再生成';
    protected static bool $shouldRegisterNavigation = false;

    public string $sysPvpSeasonId = '';
    public ?array $rankingData = [];

    protected $queryString = [
        'sysPvpSeasonId',
    ];

    private PvpCacheService $pvpCacheService;

    public function __construct()
    {
        $this->pvpCacheService = app(PvpCacheService::class);
    }

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->initializeBreadcrumbList();
    }

    private function initializeBreadcrumbList(): void
    {
        if (empty($this->breadcrumbList)) {
            $this->breadcrumbList = [
                PvpRankingResource::getUrl() => 'ランクマッチランキング',
                PvpRankingRegenerations::getUrl(['sysPvpSeasonId' => $this->sysPvpSeasonId]) => 'ランクマッチランキング再生成',
            ];
        }
    }

    public function Form(Form $form): Form
    {
        return $form
            ->schema([
                Actions::make([
                    Action::make('regenerate')
                        ->label('ランキング再生成')
                        ->requiresConfirmation()
                        ->modalHeading('ランキング再生成の確認')
                        ->modalDescription('ランクマッチランキングを再生成します。よろしいですか?')
                        ->modalSubmitActionLabel('実行')
                        ->action(fn () => $this->rankingRegeneration()),
                    Action::make('ranking')
                        ->label('ランキング表示')
                        ->action(function () {
                            $this->rankingData = $this->ranking()->toArray();
                        }),
                ])
            ]);
    }

    private function getRankingTargetPvpRankClassTypes(string $rankingMinPvpRankClass): array
    {
        $minRankClassType = PvpRankClassType::tryFrom($rankingMinPvpRankClass);
        if (is_null($minRankClassType)) {
            return [];
        }

        $targets = [];
        foreach (PvpRankClassType::cases() as $pvpRankClassType) {
            if ($minRankClassType->order() <= $pvpRankClassType->order()) {
                $targets[] = $pvpRankClassType->value;
            }
        }
        return $targets;
    }

    public function rankingRegeneration()
    {
        if ( !$this->sysPvpSeasonId) {
            Notification::make()
                ->title('ランクマッチシーズンIDが存在していません。')
                ->danger()
                ->send();

            $this->redirect(
                PvpRankingRegenerations::getUrl([
                    'sysPvpSeasonId' => $this->sysPvpSeasonId,
                ]),
            );
            return;
        }
        $sysPvpSeason = SysPvpSeason::query()->where('id', $this->sysPvpSeasonId)->first();
        $mstPvp = MstPvp::query()->where('id', $sysPvpSeason->id)->first();
        if (is_null($mstPvp)) {
            $mstPvp = MstPvp::query()->where('id', PvpConstant::DEFAULT_MST_PVP_ID)->first();
        }
        $rankingTargetRankClasses = $this->getRankingTargetPvpRankClassTypes($mstPvp->ranking_min_pvp_rank_class);

        try {
            UsrPvp::query()
                ->where('sys_pvp_season_id', $this->sysPvpSeasonId)
                ->whereIn('pvp_rank_class_type', $rankingTargetRankClasses)
                ->whereNotNull('last_played_at')
                ->chunk(10000, function($usrPvps) {
                    $userData = [];
                    foreach ($usrPvps as $usrPvp) {
                        /** @var UsrPvp $usrPvp */
                        $userData[$usrPvp->usr_user_id] = $usrPvp->getScoreForRankingRegistration();
                    }
                    $this->pvpCacheService->addRankingScoreAll($this->sysPvpSeasonId, $userData);
            });

            Notification::make()
                ->title('ランクマッチランキングを再生成しました。')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('ランクマッチシーズンID:' . $this->sysPvpSeasonId . ' ランキング再生成エラー', [$e]);
            Notification::make()
                ->title('ランクマッチランキングの再生成に失敗しました。')
                ->danger()
                ->send();
        }

        $this->redirect(
            PvpRankingRegenerations::getUrl([
                'sysPvpSeasonId' => $this->sysPvpSeasonId,
            ]),
        );
    }

    public function ranking()
    {
        if (!$this->sysPvpSeasonId) {
            return collect();
        }

        $usrUserIdScoreMap = $this->pvpCacheService->getTopRankedPlayerScoreMap(
            $this->sysPvpSeasonId,
            PvpConstant::RANKING_DISPLAY_LIMIT
        );

        //取得したユーザーのusr_profilesを取得
        $usrUserIds = collect(array_keys($usrUserIdScoreMap));

        $usrUserProfiles = UsrUserProfile::query()
            ->whereIn('usr_user_id', $usrUserIds)
            ->get()
            ->keyBy(fn(UsrUserProfile $usrUserProfile) => $usrUserProfile->getUsrUserId());

        $pvpRankingEntityList = $this->generatePvpRankingEntityList(
            $usrUserIdScoreMap,
            $usrUserProfiles,
        );

        $ranking = $pvpRankingEntityList
            ->map(fn (PvpRankingEntity $rankingEntity) => $rankingEntity->formatToResponse());

        // mountでセットした$this->>breadcrumbListが初期化されているようなので再セット
        $this->initializeBreadcrumbList();

        return $ranking;
    }

    /**
     * ランキングデータリストを生成
     * @param array<string, float>                                        $usrUserIdScoreMap usr_user_id => score
     * @param Collection<\App\Domain\User\Models\UsrUserProfileInterface> $usrUserProfiles
     * @return Collection<PvpRankingEntity>
     */
    private function generatePvpRankingEntityList(
        array $usrUserIdScoreMap,
        Collection $usrUserProfiles,
    ): Collection {
        $pvpRankingEntityList = collect();
        $rank = 0;
        $prevScore = 0;
        $sameScoreCount = 1;

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
            /** @var \App\Domain\User\Models\UsrUserProfileInterface $usrUserProfile */
            $usrUserProfile = $usrUserProfiles->get($rankerUsrUserId);

            $pvpRankingEntity = new PvpRankingEntity(
                $rank,
                $rankerUsrUserId,
                $usrUserProfile?->getName() ?? '',
                $score,
            );

            $pvpRankingEntityList->push($pvpRankingEntity);
            $prevScore = $score;
        }
        return $pvpRankingEntityList;
    }
}
