<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use Filament\Forms\Components\DateTimePicker;
use App\Models\Usr\UsrStageEnhance;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserStageEnhance extends UserDataBasePage
{
    use Authorizable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::ENHANCE_QUEST->value;

    public string $stageId = '';

    protected $queryString = [
        'userId',
        'stageId',
    ];

    public int $clear_count = 0;
    public int $reset_challenge_count = 0;
    public int $reset_ad_challenge_count = 0;
    public int $max_score = 0;
    public string $latest_reset_at = '';

    public function mount()
    {
        parent::mount();
        $usrStageEnhance = UsrStageEnhance::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_stage_id', $this->stageId)
            ->first();

        $this->clear_count = $usrStageEnhance->clear_count ?? 0;
        $this->reset_challenge_count = $usrStageEnhance->reset_challenge_count ?? 0;
        $this->reset_ad_challenge_count = $usrStageEnhance->reset_ad_challenge_count ?? 0;
        $this->max_score = $usrStageEnhance->max_score ?? 0;
        $this->latest_reset_at = $usrStageEnhance->latest_reset_at ?? '';

        $this->form->fill([
            'clear_count' => $this->clear_count,
            'reset_challenge_count' => $this->reset_challenge_count,
            'reset_ad_challenge_count' => $this->reset_ad_challenge_count,
            'max_score' => $this->max_score,
            'latest_reset_at' => $this->latest_reset_at,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserEnhanceQuest::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('clear_count')->label('クリア回数')->numeric()->minValue(0),
            TextInput::make('reset_challenge_count')->label('通常の挑戦回数')->numeric()->minValue(0),
            TextInput::make('reset_ad_challenge_count')->label('広告視聴による挑戦回数')->numeric()->minValue(0),
            TextInput::make('max_score')->label('最大スコア')->numeric()->minValue(0),
            DateTimePicker::make('latest_reset_at')->label('最終リセット日時')->seconds(false),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrStageEnhance::upsert(
            [
                'usr_user_id' => $this->userId,
                'mst_stage_id' => $this->stageId,
                'clear_count' => $this->form->getState()['clear_count'],
                'reset_challenge_count' => $this->form->getState()['reset_challenge_count'],
                'reset_ad_challenge_count' => $this->form->getState()['reset_ad_challenge_count'],
                'max_score' => $this->form->getState()['max_score'],
                'latest_reset_at' => $this->form->getState()['latest_reset_at'],
            ],
            ['usr_user_id', 'mst_stage_id'],
            [
                'clear_count' => $this->form->getState()['clear_count'],
                'reset_challenge_count' => $this->form->getState()['reset_challenge_count'],
                'reset_ad_challenge_count' => $this->form->getState()['reset_ad_challenge_count'],
                'max_score' => $this->form->getState()['max_score'],
                'latest_reset_at' => $this->form->getState()['latest_reset_at'],
            ],
        );

        $this->redirectRoute('filament.admin.pages.user-enhance-quest', ['userId' => $this->userId]);
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
