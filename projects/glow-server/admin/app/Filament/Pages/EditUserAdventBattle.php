<?php

namespace App\Filament\Pages;

use App\Constants\RewardReceiptStatusType;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrAdventBattle;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;


class EditUserAdventBattle extends UserDataBasePage
{
    use Authorizable;
    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::ADVENT_BATTLE->value;

    public string $mstAdventBattleId = '';

    protected $queryString = [
        'userId',
        'mstAdventBattleId',
    ];

    public int $isValid;
    public int $partyNo;
    public int $maxScore;
    public int $totalScore;
    public int $challengeCount;
    public int $resetChallengeCount;
    public int $resetAdChallengeCount;
    public int $maxReceivedMaxScoreReward;
    public int $isRankingRewardReceived;
    public ?string $latestResetAt;

    public function mount()
    {
        parent::mount();

        /** @var UsrAdventBattle|null $usrShopItem */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $this->userId)
            ->where('mst_advent_battle_id', $this->mstAdventBattleId)
            ->first();

        $this->maxScore = $usrAdventBattle?->max_score ?? 0;
        $this->totalScore = $usrAdventBattle?->total_score ?? 0;
        $this->challengeCount = $usrAdventBattle?->challenge_count ?? 0;
        $this->resetChallengeCount = $usrAdventBattle?->reset_challenge_count ?? 0;
        $this->resetAdChallengeCount = $usrAdventBattle?->reset_ad_challenge_count ?? 0;
        $this->maxReceivedMaxScoreReward = $usrAdventBattle?->max_received_max_score_reward ?? 0;
        $this->isRankingRewardReceived = $usrAdventBattle?->is_ranking_reward_received ?? 0;
        $this->latestResetAt = $usrAdventBattle?->latest_reset_at;

        $this->form->fill([
            'max_score' => $this->maxScore,
            'total_score' => $this->totalScore,
            'challenge_count' => $this->challengeCount,
            'reset_challenge_count' => $this->resetChallengeCount,
            'reset_ad_challenge_count' => $this->resetAdChallengeCount,
            'max_received_max_score_reward' => $this->maxReceivedMaxScoreReward ,
            'is_ranking_reward_received' => $this->isRankingRewardReceived,
            'latest_reset_at' => $this->latestResetAt,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserAdventBattle::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstAdventBattleId' => $this->mstAdventBattleId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('maxScore')
                ->label('最大スコア')
                ->numeric()
                ->minValue(0),
            TextInput::make('totalScore')
                ->label('合計スコア')
                ->numeric()
                ->minValue(0),
            TextInput::make('challengeCount')
                ->label('通算挑戦回数')
                ->numeric()
                ->minValue(0)
                ->maxValue(9999)
                ->helperText('挑戦回数(リセットなし)'),
            TextInput::make('resetChallengeCount')
                ->label('デイリー挑戦回数')
                ->numeric()
                ->minValue(0)
                ->maxValue(9999)
                ->helperText('挑戦回数(デイリーリセット対象)'),
            TextInput::make('resetAdChallengeCount')
                ->label('デイリー挑戦回数(広告視聴)')
                ->numeric()
                ->minValue(0)
                ->maxValue(9999)
                ->helperText('広告視聴での挑戦回数(デイリーリセット対象)'),
            TextInput::make('maxReceivedMaxScoreReward')
                ->label('最大スコア報酬')
                ->numeric()
                ->minValue(0)
                ->helperText('受取済みの最大スコア報酬の最大スコア'),
            Select::make('isRankingRewardReceived')
                ->label('報酬受取り状況')
                ->options(RewardReceiptStatusType::labels()),
            DateTimePicker::make('latestResetAt')
                ->label('リセット日時'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrAdventBattle::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_advent_battle_id', $this->mstAdventBattleId)
            ->update([
                'max_score' => $this->form->getState()['maxScore'],
                'total_score' => $this->form->getState()['totalScore'],
                'challenge_count' => $this->form->getState()['challengeCount'],
                'reset_challenge_count' => $this->form->getState()['resetChallengeCount'],
                'reset_ad_challenge_count' => $this->form->getState()['resetAdChallengeCount'],
                'max_received_max_score_reward' => $this->form->getState()['maxReceivedMaxScoreReward'],
                'is_ranking_reward_received' => $this->form->getState()['isRankingRewardReceived'] ?? 0,
                'latest_reset_at' => $this->form->getState()['latestResetAt'],
            ]);

        $this->redirectRoute('filament.admin.pages.user-advent-battle', ['userId' => $this->userId]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('update')
                ->label('更新')
                ->requiresConfirmation()
                ->action(fn () => $this->update())
        ];
    }
}
