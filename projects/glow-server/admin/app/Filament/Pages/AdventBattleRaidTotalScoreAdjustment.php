<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Filament\Authorizable;
use App\Models\Mst\MstAdventBattle;
use App\Services\AdventBattleCacheService;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class AdventBattleRaidTotalScoreAdjustment extends Page
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static string $view = 'filament.pages.advent-battle-raid-total-score-adjustment';
    protected static ?string $title = '降臨バトル協力スコア調整';
    protected static bool $shouldRegisterNavigation = true;

    public string $mstAdventBattleId = '';
    public ?int $newScore = null;
    public ?array $currentScoreData = null;

    protected $queryString = [
        'mstAdventBattleId',
    ];

    private AdventBattleCacheService $adventBattleCacheService;

    public function __construct()
    {
        $this->adventBattleCacheService = app(AdventBattleCacheService::class);
    }

    public function Form(Form $form): Form
    {
        $mstAdventBattleOptions = MstAdventBattle::with('mst_advent_battle_i18n')
            ->orderBy('start_at', 'desc')
            ->get()
            ->mapWithKeys(fn ($mstAdventBattle) => [
                $mstAdventBattle->id => '[' . $mstAdventBattle->id . ']' . ($mstAdventBattle->mst_advent_battle_i18n?->name ?? (string) $mstAdventBattle->id),
            ])
            ->all();

        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('mstAdventBattleId')
                            ->label('降臨バトルID')
                            ->required()
                            ->options($mstAdventBattleOptions)
                            ->searchable()
                            ->placeholder('降臨バトルを選択してください')
                            ->live()
                            ->afterStateUpdated(fn () => $this->reset(['currentScoreData', 'newScore'])),
                        TextInput::make('newScore')
                            ->label('新しいスコア')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(999999999999999) // 999兆（JS Number.MAX_SAFE_INTEGER未満で安全に扱える上限）
                            ->live(debounce: 500)
                            ->placeholder('例: 1000000'),
                    ]),
                Actions::make([
                    Action::make('viewCurrentScore')
                        ->label('現在スコア確認')
                        ->action(fn () => $this->viewCurrentScore()),
                    Action::make('setScore')
                        ->label('スコアを設定')
                        ->requiresConfirmation()
                        ->modalHeading('スコア設定の確認')
                        ->modalDescription(function () {
                            if (!$this->currentScoreData) {
                                return '現在のスコアを確認してから設定してください。';
                            }
                            return sprintf(
                                '降臨バトルID: %s のスコアを %s → %s に変更します。よろしいですか？',
                                $this->mstAdventBattleId,
                                number_format($this->currentScoreData['score']),
                                number_format($this->newScore)
                            );
                        })
                        ->disabled(fn () => !$this->currentScoreData
                            || $this->newScore === null
                            || $this->newScore === $this->currentScoreData['score'])
                        ->action(fn () => $this->setScore()),
                ])
            ]);
    }

    public function viewCurrentScore()
    {
        if (!$this->mstAdventBattleId) {
            Notification::make()
                ->title('降臨バトルIDが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        try {
            $score = $this->adventBattleCacheService->getRaidTotalScore($this->mstAdventBattleId);
            $cacheKey = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($this->mstAdventBattleId);

            $this->currentScoreData = [
                'mst_advent_battle_id' => $this->mstAdventBattleId,
                'score' => $score,
                'cache_key' => $cacheKey,
            ];

            Notification::make()
                ->title('現在スコア情報を取得しました。')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('現在スコア情報の取得に失敗しました。')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function setScore()
    {
        if (!$this->mstAdventBattleId) {
            Notification::make()
                ->title('降臨バトルIDが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        if ($this->newScore === null) {
            Notification::make()
                ->title('新しいスコアが入力されていません。')
                ->danger()
                ->send();
            return;
        }

        if ($this->newScore < 0) {
            Notification::make()
                ->title('スコアは0以上の数値を入力してください。')
                ->danger()
                ->send();
            return;
        }

        try {
            $oldScore = $this->adventBattleCacheService->getRaidTotalScore($this->mstAdventBattleId);
            $newScore = $this->newScore;

            $this->adventBattleCacheService->setRaidTotalScore($this->mstAdventBattleId, $newScore);

            // 操作ログを記録
            Log::info('AdventBattle RaidTotalScore adjusted', [
                'mst_advent_battle_id' => $this->mstAdventBattleId,
                'old_score' => $oldScore,
                'new_score' => $newScore,
                'user' => auth()->user()?->name ?? 'Unknown',
            ]);

            // スコア設定後、自動的に現在スコアを更新
            $this->viewCurrentScore();

            Notification::make()
                ->title('スコアを設定しました。')
                ->body(sprintf('変更前: %s → 変更後: %s', number_format($oldScore), number_format($newScore)))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('スコアの設定に失敗しました。')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
