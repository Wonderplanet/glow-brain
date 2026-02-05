<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrBoxGacha;
use App\Traits\NotificationTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserBoxGacha extends UserDataBasePage
{
    use Authorizable;
    use NotificationTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::BOX_GACHA->value;

    public string $mstBoxGachaId = '';

    protected $queryString = [
        'userId',
        'mstBoxGachaId',
    ];

    public int $current_box_level;
    public int $reset_count;
    public int $draw_count;
    public int $total_draw_count;

    public function mount()
    {
        parent::mount();

        /** @var UsrBoxGacha|null $usrBoxGacha */
        $usrBoxGacha = UsrBoxGacha::where('usr_user_id', $this->userId)
            ->where('mst_box_gacha_id', $this->mstBoxGachaId)
            ->first();

        if ($usrBoxGacha === null) {
            $this->sendDangerNotification(
                title: 'BOXガシャが見つかりません',
                body: 'BOXガシャID: ' . $this->mstBoxGachaId,
            );
            $this->redirect(UserBoxGacha::getUrl(['userId' => $this->userId]));
            return;
        }

        $this->current_box_level = $usrBoxGacha->current_box_level;
        $this->reset_count = $usrBoxGacha->reset_count;
        $this->draw_count = $usrBoxGacha->draw_count;
        $this->total_draw_count = $usrBoxGacha->total_draw_count;

        $this->form->fill([
            'current_box_level' => $this->current_box_level,
            'reset_count' => $this->reset_count,
            'draw_count' => $this->draw_count,
            'total_draw_count' => $this->total_draw_count,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserBoxGacha::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstBoxGachaId' => $this->mstBoxGachaId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Group::make()
                ->schema([
                    TextInput::make('current_box_level')
                        ->label('現在BOXレベル')
                        ->numeric()
                        ->disabled(),
                    TextInput::make('reset_count')
                        ->label('リセット回数')
                        ->numeric()
                        ->disabled(),
                    TextInput::make('draw_count')
                        ->label('現在BOX抽選回数')
                        ->numeric()
                        ->disabled(),
                    TextInput::make('total_draw_count')
                        ->label('総抽選回数')
                        ->numeric()
                        ->disabled(),
                    ],
                )
                ->columns(4),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        $updatedCount = UsrBoxGacha::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_box_gacha_id', $this->mstBoxGachaId)
            ->update([
                'current_box_level' => 1,
                'reset_count' => 0,
                'draw_count' => 0,
                'total_draw_count' => 0,
                'draw_prizes' => '{}',
            ]);

        if ($updatedCount === 0) {
            $this->sendDangerNotification(
                title: '初期化に失敗しました',
                body: 'BOXガシャID: ' . $this->mstBoxGachaId,
            );
            $this->redirectRoute('filament.admin.pages.user-box-gacha', ['userId' => $this->userId]);
            return;
        }

        $this->sendProcessCompletedNotification(
            title: 'BOXガシャデータの初期化に成功しました',
            body: 'BOXガシャID: ' . $this->mstBoxGachaId,
        );
        $this->redirectRoute('filament.admin.pages.user-box-gacha', ['userId' => $this->userId]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('initialize')
                ->label('初期化')
                ->requiresConfirmation()
                ->action(fn () => $this->update())
        ];
    }
}
