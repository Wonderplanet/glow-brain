<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrStageEvent;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserStageEvent extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::EVENT_QUEST->value;

    public string $mstStageId = '';
    public string $mstQuestId = '';

    protected $queryString = [
        'userId',
        'mstStageId',
        'mstQuestId',
    ];

    public int $clear_count;
    public int $reset_clear_count;
    public int $reset_ad_challenge_count;
    public string $latest_reset_at;

    public function mount()
    {
        parent::mount();
        $userParameter = UsrStageEvent::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_stage_id', $this->mstStageId)
            ->first();

        $this->clear_count              = $userParameter->clear_count;
        $this->reset_clear_count        = $userParameter->reset_clear_count;
        $this->reset_ad_challenge_count = $userParameter->reset_ad_challenge_count;
        $this->latest_reset_at          = $userParameter->latest_reset_at ?? '';

        $this->form->fill([
            'clear_count'               => $this->clear_count,
            'reset_clear_count'         => $this->reset_clear_count,
            'reset_ad_challenge_count'  => $this->reset_ad_challenge_count,
            'latest_reset_at'           => $this->latest_reset_at,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserStageEvent::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstStageId' => $this->mstStageId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('clear_count')->label('クリア回数')->numeric()->minValue(0),
            TextInput::make('reset_clear_count')->label('リセットからのクリア回数')->numeric()->minValue(0),
            TextInput::make('reset_ad_challenge_count')->label('リセットからの広告視聴での挑戦回数')->numeric()->minValue(0),
            DateTimePicker::make('latest_reset_at')->label('リセット日時')->seconds(false),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrStageEvent::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_stage_id', $this->mstStageId)
            ->update([
                'clear_count' => $this->form->getState()['clear_count'],
                'reset_clear_count' => $this->form->getState()['reset_clear_count'],
                'reset_ad_challenge_count' => $this->form->getState()['reset_ad_challenge_count'],
                'latest_reset_at' => $this->form->getState()['latest_reset_at'],
            ]);
        return redirect(UserStageEvent::getUrl(['userId' => $this->userId, 'mstQuestId' => $this->mstQuestId]));
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
