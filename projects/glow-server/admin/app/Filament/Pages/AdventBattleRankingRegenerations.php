<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Services\AdventBattleCacheService as baseAdventBattleCacheService;
use App\Entities\AdventBattleRankingEntity;
use App\Filament\Resources\AdventBattleRankingResource;
use App\Models\Mst\MstUnit;
use App\Models\Usr\UsrAdventBattle;
use App\Models\Usr\UsrUserProfile;
use App\Services\AdventBattleCacheService;
use App\Traits\LogUserPartyTrait;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AdventBattleRankingRegenerations extends Page
{
    use LogUserPartyTrait;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static string $view = 'filament.pages.advent-battle-ranking-regenerations';
    protected static ?string $title = '降臨バトルランキング再生成';
    protected static bool $shouldRegisterNavigation = false;

    public string $mstAdventBattleId = '';
    public ?array $rankingData = [];

    protected $queryString = [
        'mstAdventBattleId',
    ];

    private baseAdventBattleCacheService $baseAdventBattleCacheService;
    private AdventBattleCacheService $adventBattleCacheService;

    public function __construct()
    {
        $this->baseAdventBattleCacheService = app(baseAdventBattleCacheService::class);
        $this->adventBattleCacheService = app(AdventBattleCacheService::class);
    }

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            AdventBattleRankingResource::getUrl() => '降臨バトル',
            AdventBattleRankingRegenerations::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]) => '降臨バトルランキング再生成',
        ]);
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
                        ->modalDescription('降臨バトルランキングを再生成します。よろしいですか?')
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

    public function rankingRegeneration() {

        $mstAdventBattleId = $this->mstAdventBattleId;

        if ( !$mstAdventBattleId) {
            Notification::make()
                ->title('降臨バトルIDが存在していません。')
                ->danger()
                ->send();

            return $this->redirect(
                AdventBattleRankingRegenerations::getUrl([
                    'mstAdventBattleId' => $mstAdventBattleId,
                ]),
            );
        }

        try {
            UsrAdventBattle::query()
                ->where('mst_advent_battle_id', $mstAdventBattleId)
                ->where('max_score', '>', 0)
                ->orderBy('created_at')
                ->chunk(10000, function($usrAdventBattles) use ($mstAdventBattleId) {
                    $userData = [];
                    foreach ($usrAdventBattles as $usrAdventBattle) {
                        $score = $usrAdventBattle->isExcludedRanking() ? AdventBattleConstant::RANKING_CHEATER_SCORE : $usrAdventBattle->max_score;
                        $userData[$usrAdventBattle->usr_user_id] = $score;
                    }
                    $this->adventBattleCacheService->addRankingScoreAll($mstAdventBattleId, $userData);
            });

            Notification::make()
                ->title('降臨バトルランキングを再生成しました。')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('降臨バトルID:'.$mstAdventBattleId.' ランキング再生成エラー', [$e]);
            Notification::make()
                ->title('降臨バトルランキングを再生成に失敗しました。')
                ->danger()
                ->send();
        }

        $this->redirect(
            AdventBattleRankingRegenerations::getUrl([
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

        $usrUserProfiles = UsrUserProfile::query()
            ->whereIn('usr_user_id', $usrUserIds)
            ->get()
            ->keyBy(fn(UsrUserProfile $usrUserProfile) => $usrUserProfile->getUsrUserId());

        $usrAdventBattles = UsrAdventBattle::query()
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->whereIn('usr_user_id', $usrUserIds)
            ->get()
            ->keyBy(fn(UsrAdventBattle $usrAdventBattle) => $usrAdventBattle->getUsrUserId());

        $adventBattleRankingEntityList = $this->generateAdventBattleRankingEntityList(
            $usrUserIdScoreMap,
            $usrUserProfiles,
            $usrAdventBattles
        );

        $ranking = $adventBattleRankingEntityList
            ->map(fn (AdventBattleRankingEntity $rankingEntity) => $rankingEntity->formatToResponse());

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            AdventBattleRankingResource::getUrl() => '降臨バトル',
            AdventBattleRankingRegenerations::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]) => '降臨バトルランキング再生成',
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

        $mstUnits = MstUnit::query()
            ->whereIn('id', $mstUnitIds)
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

            /** @var UsrAdventBattleInterface $usrAdventBattle */
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
