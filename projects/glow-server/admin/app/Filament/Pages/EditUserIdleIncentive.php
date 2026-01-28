<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrIdleIncentive;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserIdleIncentive extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::IDLE_INCENTIVE->value;

    public string $diamondQuickReceiveCount;
    public string $adQuickReceiveCount;
    public string $idleStartedAt;
    public string $diamondQuickReceiveAt;
    public string $adQuickReceiveAt;

    public function mount()
    {
        parent::mount();
        $userIdleIncentive = UsrIdleIncentive::where('usr_user_id', $this->userId)->first();
        $this->diamondQuickReceiveCount = $userIdleIncentive->diamond_quick_receive_count;
        $this->adQuickReceiveCount = $userIdleIncentive->ad_quick_receive_count;
        $this->idleStartedAt = $userIdleIncentive->idle_started_at;
        $this->diamondQuickReceiveAt = $userIdleIncentive->diamond_quick_receive_at;
        $this->adQuickReceiveAt = $userIdleIncentive->ad_quick_receive_at;

        $this->form->fill([
            'diamondQuickReceiveCount' => $this->diamondQuickReceiveCount,
            'adQuickReceiveCount' => $this->adQuickReceiveCount,
            'idleStartedAt' => $this->idleStartedAt,
            'diamondQuickReceiveAt' => $this->diamondQuickReceiveAt,
            'adQuickReceiveAt' => $this->adQuickReceiveAt,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserIdleIncentive::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId]) => '編集',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema(
            [
                DateTimePicker::make('idleStartedAt')->label('探索開始日時'),
                TextInput::make('diamondQuickReceiveCount')->label('プリズムクイック探索回数'),
                DateTimePicker::make('diamondQuickReceiveAt')->label('プリズムクイック探索獲得日時'),
                TextInput::make('adQuickReceiveCount')->label('動画広告クイック探索回数'),
                DateTimePicker::make('adQuickReceiveAt')->label('動画広告クイック探索獲得日時'),
            ]
        );
    }

    public function update()
    {
        UsrIdleIncentive::query()
            ->where('usr_user_id', $this->userId)
            ->update([
                'diamond_quick_receive_count' => $this->form->getState()['diamondQuickReceiveCount'],
                'ad_quick_receive_count' => $this->form->getState()['adQuickReceiveCount'],
                'idle_started_at' => $this->form->getState()['idleStartedAt'],
                'diamond_quick_receive_at' => $this->form->getState()['diamondQuickReceiveAt'],
                'ad_quick_receive_at' => $this->form->getState()['adQuickReceiveAt'],
            ]);
        $this->redirectRoute('filament.admin.pages.user-idle-incentive', ['userId' => $this->userId]);
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
