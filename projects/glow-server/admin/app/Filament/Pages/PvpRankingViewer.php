<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Entities\PvpRankingEntity;
use App\Filament\Authorizable;
use App\Filament\Resources\PvpRankingResource;
use App\Models\Usr\UsrUserProfile;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class PvpRankingViewer extends Page
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static string $view = 'filament.pages.pvp-ranking-viewer';
    protected static ?string $title = 'ランクマッチランキング閲覧';
    protected static bool $shouldRegisterNavigation = true;

    public string $sysPvpSeasonId = '';
    public string $usrUserId = '';
    public ?array $userRankingData = null;
    public ?array $rankingTtlData = null;

    protected $queryString = [
        'sysPvpSeasonId',
        'usrUserId',
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
                PvpRankingViewer::getUrl([
                    'sysPvpSeasonId' => $this->sysPvpSeasonId,
                    'usrUserId' => $this->usrUserId
                ]) => 'ランクマッチランキング閲覧',
            ];
        }
    }

    public function Form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('sysPvpSeasonId')
                            ->label('ランクマッチシーズンID')
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
        if (!$this->sysPvpSeasonId) {
            Notification::make()
                ->title('ランクマッチシーズンIDが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        try {
            if ($this->usrUserId) {
                // 個別ユーザーのランキング情報を取得
                $score = $this->pvpCacheService->getRankingScore($this->sysPvpSeasonId, $this->usrUserId);
                $rank = $this->pvpCacheService->getMyRanking($this->usrUserId, $this->sysPvpSeasonId);

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
                        'sys_pvp_season_id' => $this->sysPvpSeasonId,
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
        if (!$this->sysPvpSeasonId) {
            Notification::make()
                ->title('ランクマッチシーズンIDが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        try {
            $ttl = $this->pvpCacheService->getPvpRankingCacheTtl($this->sysPvpSeasonId);
            $cacheKey = CacheKeyUtil::getPvpRankingKey($this->sysPvpSeasonId);

            $this->rankingTtlData = [
                'sys_pvp_season_id' => $this->sysPvpSeasonId,
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
