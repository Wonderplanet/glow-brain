<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrPvp;
use App\Services\PvpRankService;
use App\Traits\NotificationTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class EditUserPvp extends UserDataBasePage
{
    use Authorizable;
    use NotificationTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::PVP->value;

    public ?string $sysPvpSeasonId = null;
    protected $queryString = [
        'userId',
        'sysPvpSeasonId',
    ];

    // formパラメータ
    public int $score = 0;
    public int $dailyRemainingChallengeCount = 0;
    public int $dailyRemainingItemChallengeCount = 0;
    public int $isSeasonRewardReceived = 0;

    // 編集しないけど表示するパラメータ
    public string $pvpRankClassType = '';
    public int $pvpRankClassLevel = 0;
    public ?int $ranking = 0;
    public ?string $lastPlayedAt = '';
    public ?string $selectedOpponentCandidateFirst = '';
    public ?string $selectedOpponentCandidateSecond = '';
    public ?string $selectedOpponentCandidateThird = '';
    public string $isExcludedRanking = '';

    public function mount(): void
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserPvp::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'sysPvpSeasonId' => $this->sysPvpSeasonId]) => '編集',
        ]);
        $this->setFormValues();
    }

    private function setFormValues(): void
    {
        $usrPvp = UsrPvp::where('usr_user_id', $this->userId)
            ->where('sys_pvp_season_id', $this->sysPvpSeasonId)
            ->first();

        if ($usrPvp === null) {
            $this->redirect(
                UserParty::getUrl(['userId' => $this->userId]),
            );
            $this->sendDangerNotification(
                title: 'データが見つかりませんでした',
                body: 'ランクマッチシーズンID: ' . $this->sysPvpSeasonId,
            );
            return;
        }

        $this->score = $usrPvp->score;
        $this->dailyRemainingChallengeCount = $usrPvp->daily_remaining_challenge_count;
        $this->dailyRemainingItemChallengeCount = $usrPvp->daily_remaining_item_challenge_count;
        $this->isSeasonRewardReceived = $usrPvp->is_season_reward_received;

        $this->pvpRankClassType = $usrPvp->pvp_rank_class_type;
        $this->pvpRankClassLevel = $usrPvp->pvp_rank_class_level;
        $this->ranking = $usrPvp->ranking;
        $this->lastPlayedAt = $usrPvp->last_played_at;
        $selectedOpponentCandidates = array_values($usrPvp->selected_opponent_candidates);
        $selectedOpponentCandidateFirst = $selectedOpponentCandidates[0] ?? [];
        $selectedOpponentCandidateSecond = $selectedOpponentCandidates[1] ?? [];
        $selectedOpponentCandidateThird = $selectedOpponentCandidates[2] ?? [];
        $this->selectedOpponentCandidateFirst = json_encode($selectedOpponentCandidateFirst);
        $this->selectedOpponentCandidateSecond = json_encode($selectedOpponentCandidateSecond);
        $this->selectedOpponentCandidateThird = json_encode($selectedOpponentCandidateThird);
        $this->isExcludedRanking = $usrPvp->is_excluded_ranking ? '除外' : '非除外';

        $this->form->fill([
            'score' => $this->score,
            'dailyRemainingChallengeCount' => $this->dailyRemainingChallengeCount,
            'dailyRemainingItemChallengeCount' => $this->dailyRemainingItemChallengeCount,
            'isSeasonRewardReceived' => $this->isSeasonRewardReceived,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(4)->schema([
                TextInput::make('sysPvpSeasonId')->label('ランクマッチシーズンID')->disabled(),
                TextInput::make('pvpRankClassType')->label('ランククラス種別')->disabled(),
                TextInput::make('pvpRankClassLevel')->label('ランククラスレベル')->disabled(),
                DateTimePicker::make('lastPlayedAt')->label('最終プレイ日時')->disabled(),
                TextInput::make('ranking')->label('ランキング')->disabled(),
                TextInput::make('isExcludedRanking')->label('ランキングから除外されているか')->disabled(),
            ]),
            Grid::make(3)->schema([
                Textarea::make('selectedOpponentCandidateFirst')->label('選出した対戦相手の情報1')->disabled(),
                Textarea::make('selectedOpponentCandidateSecond')->label('選出した対戦相手の情報2')->disabled(),
                Textarea::make('selectedOpponentCandidateThird')->label('選出した対戦相手の情報3')->disabled(),
            ]),
            TextInput::make('score')->label('ランクポイント'),
            TextInput::make('dailyRemainingChallengeCount')->label('残りアイテム消費なし挑戦可能回数'),
            TextInput::make('dailyRemainingItemChallengeCount')->label('残りアイテム消費あり挑戦可能回数'),
            Toggle::make('isSeasonRewardReceived')->label('シーズン報酬受け取り済みか')->onColor('success')->offColor('danger'),
        ]);
    }

    public function update()
    {
        $score = $this->form->getState()['score'];
        $pvpRankService = app(PvpRankService::class);
        $mstPvpRank = $pvpRankService->getPvpRankByScore($score);
        if (empty($mstPvpRank)) {
            $this->sendDangerNotification(
                title: 'ランククラスの取得に失敗しました',
                body: 'スコア: ' . $score,
            );
            return;
        }

        UsrPvp::query()
            ->where('usr_user_id', $this->userId)
            ->where('sys_pvp_season_id', $this->sysPvpSeasonId)
            ->update([
                'score' => $score,
                'pvp_rank_class_type' => $mstPvpRank->rank_class_type,
                'pvp_rank_class_level' => $mstPvpRank->rank_class_level,
                'daily_remaining_challenge_count' => $this->form->getState()['dailyRemainingChallengeCount'],
                'daily_remaining_item_challenge_count' => $this->form->getState()['dailyRemainingItemChallengeCount'],
                'is_season_reward_received' => $this->form->getState()['isSeasonRewardReceived'],
            ]);

        $this->sendProcessCompletedNotification(
            title: 'ランクマッチデータの更新に成功しました',
            body: 'ランクマッチシーズンID: ' . $this->sysPvpSeasonId,
        );
        $this->redirectRoute('filament.admin.pages.user-pvp', ['userId' => $this->userId]);
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
