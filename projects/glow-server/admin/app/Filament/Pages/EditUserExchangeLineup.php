<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrExchangeLineup;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class EditUserExchangeLineup extends UserDataBasePage
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::EXCHANGE->value;

    public string $mstExchangeId = '';

    public string $mstExchangeLineupId = '';

    protected $queryString = [
        'userId',
        'mstExchangeId',
        'mstExchangeLineupId',
    ];

    public int $trade_count;

    public function mount()
    {
        parent::mount();

        /** @var UsrExchangeLineup|null $usrExchangeLineup */
        $usrExchangeLineup = UsrExchangeLineup::where('usr_user_id', $this->userId)
            ->where('mst_exchange_id', $this->mstExchangeId)
            ->where('mst_exchange_lineup_id', $this->mstExchangeLineupId)
            ->first();

        if ($usrExchangeLineup === null) {
            Notification::make()
                ->title('交換ラインナップが見つかりません')
                ->body('指定された交換ラインナップのレコードが存在しないため、編集できません。')
                ->danger()
                ->send();

            $this->redirect(UserExchangeLineup::getUrl(['userId' => $this->userId]));
            return;
        }

        $this->trade_count = $usrExchangeLineup->trade_count;

        $this->form->fill([
            'trade_count' => $this->trade_count,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserExchangeLineup::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstExchangeId' => $this->mstExchangeId, 'mstExchangeLineupId' => $this->mstExchangeLineupId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('trade_count')
                ->label('交換した回数')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(9999),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        $updatedCount = UsrExchangeLineup::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_exchange_id', $this->mstExchangeId)
            ->where('mst_exchange_lineup_id', $this->mstExchangeLineupId)
            ->update([
                'trade_count' => $this->form->getState()['trade_count'],
            ]);

        if ($updatedCount === 0) {
            Notification::make()
                ->title('更新に失敗しました')
                ->body('交換ラインナップのレコードが見つからないため、更新できませんでした。')
                ->danger()
                ->send();

            $this->redirectRoute('filament.admin.pages.user-exchange-lineup', ['userId' => $this->userId]);
            return;
        }

        Notification::make()
            ->title('更新しました')
            ->success()
            ->send();

        $this->redirectRoute('filament.admin.pages.user-exchange-lineup', ['userId' => $this->userId]);
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
