<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Filament\Authorizable;
use App\Filament\Resources\AdventBattleRankingResource;
use App\Models\Usr\UsrUserProfile;
use App\Services\AdventBattleCacheService;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AdventBattleRankingViewer extends Page
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static string $view = 'filament.pages.advent-battle-ranking-viewer';
    protected static ?string $title = '降臨バトルランキング閲覧';
    protected static bool $shouldRegisterNavigation = true;

    public string $mstAdventBattleId = '';
    public string $usrUserId = '';
    public ?array $userRankingData = null;
    public ?array $rankingTtlData = null;

    protected $queryString = [
        'mstAdventBattleId',
        'usrUserId',
    ];

    private AdventBattleCacheService $adventBattleCacheService;

    public function __construct()
    {
        $this->adventBattleCacheService = app(AdventBattleCacheService::class);
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
                AdventBattleRankingResource::getUrl() => '降臨バトルランキング',
                AdventBattleRankingViewer::getUrl([
                    'mstAdventBattleId' => $this->mstAdventBattleId,
                    'usrUserId' => $this->usrUserId
                ]) => '降臨バトルランキング閲覧',
            ];
        }
    }

    public function Form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('mstAdventBattleId')
                            ->label('降臨バトルID')
                            ->required(),
                        TextInput::make('usrUserId')
                            ->label('ユーザーID')
                            ->placeholder('(オプション: 個別ユーザー検索時のみ入力)'),
                    ]),
                Actions::make([
                    Action::make('viewUserRanking')
                        ->label('ユーザーランキング確認')
                        ->action(fn () => $this->viewUserRanking()),
                    Action::make('viewTtl')
                        ->label('TTL確認')
                        ->action(fn () => $this->viewTtl()),
                ])
            ]);
    }

    public function viewUserRanking()
    {
        if (!$this->mstAdventBattleId) {
            Notification::make()
                ->title('降臨バトルIDが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        try {
            if ($this->usrUserId) {
                // 個別ユーザーのランキング情報を取得
                $score = $this->adventBattleCacheService->getRankingScore($this->mstAdventBattleId, $this->usrUserId);
                $rank = $this->adventBattleCacheService->getMyRanking($this->usrUserId, $this->mstAdventBattleId);

                if ($score === null) {
                    $this->userRankingData = [
                        'message' => 'ユーザー（' . $this->usrUserId . '）のランキングデータが見つかりません。'
                    ];
                } else {
                    // ユーザープロフィール情報を取得
                    $usrUserProfile = UsrUserProfile::query()
                        ->where('usr_user_id', $this->usrUserId)
                        ->first();

                    $this->userRankingData = [
                        'usr_user_id' => $this->usrUserId,
                        'user_name' => $usrUserProfile?->getName() ?? 'Unknown',
                        'score' => $score,
                        'rank' => $rank,
                        'mst_advent_battle_id' => $this->mstAdventBattleId,
                    ];
                }
            } else {
                Notification::make()
                    ->title('ユーザーIDが入力されていません。')
                    ->warning()
                    ->send();
                return;
            }

            Notification::make()
                ->title('ユーザーランキング情報を取得しました。')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('ユーザーランキング情報の取得に失敗しました。')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function viewTtl()
    {
        if (!$this->mstAdventBattleId) {
            Notification::make()
                ->title('降臨バトルIDが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        try {
            $ttl = $this->adventBattleCacheService->getAdventBattleRankingCacheTtl($this->mstAdventBattleId);
            $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($this->mstAdventBattleId);

            $this->rankingTtlData = [
                'mst_advent_battle_id' => $this->mstAdventBattleId,
                'cache_key' => $cacheKey,
                'ttl_seconds' => $ttl,
                'ttl_formatted' => $this->formatTtl($ttl),
            ];

            Notification::make()
                ->title('TTL情報を取得しました。')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('TTL情報の取得に失敗しました。')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function formatTtl(int $ttl): string
    {
        if ($ttl === -1) {
            return 'TTLが設定されていません（永続）';
        } elseif ($ttl === -2) {
            return 'キーが存在しません';
        } elseif ($ttl <= 0) {
            return 'TTLが0以下（期限切れ）';
        } else {
            $days = floor($ttl / 86400);
            $hours = floor(($ttl % 86400) / 3600);
            $minutes = floor(($ttl % 3600) / 60);
            $seconds = $ttl % 60;

            return sprintf('%d日 %d時間 %d分 %d秒', $days, $hours, $minutes, $seconds);
        }
    }
}
