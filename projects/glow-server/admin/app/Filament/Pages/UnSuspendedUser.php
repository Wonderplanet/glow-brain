<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Constants\UserStatus;
use App\Filament\Pages\SuspendedUser\SuspendedUserSearch;
use App\Models\Adm\AdmUserBanOperateHistory;
use App\Models\Usr\UsrUser;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class UnSuspendedUser extends Page
{
    protected static string $view = 'filament.pages.edit-suspended-user';
    protected static ?string $title = 'アカウント一時停止解除';
    protected static bool $shouldRegisterNavigation = false;

    public string $userId = '';
    public int $status = 0;

    protected $queryString = [
        'userId',
        'status'
    ];

    public string $operationReason;

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            SuspendedUserSearch::getUrl() => 'アカウント停止',
            SuspendedUser::getUrl(['userId' => $this->userId]) => 'アカウント停止詳細',
            self::getUrl(['userId' => $this->userId, 'status' => $this->status]) => 'アカウント一時停止',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('operationReason')
                ->label('アカウント一時停止解除経緯')
                ->rows(10)
                ->required(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        $now = CarbonImmutable::now();
        AdmUserBanOperateHistory::query()->insert([
            'id' => Str::uuid(),
            'usr_user_id' => $this->userId,
            'ban_status' => UserStatus::NORMAL->value,
            'adm_user_id' => auth()->id(),
            'operation_reason' => $this->form->getState()['operationReason'],
            'operated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        UsrUser::query()
            ->where('id', $this->userId)
            ->update([
                'status' => UserStatus::NORMAL->value,
                'suspend_end_at' => null
            ]);

        Notification::make()
            ->title('アカウント一時停止を解除しました。')
            ->success()
            ->send();

        $this->redirect(
            SuspendedUser::getUrl(['userId' => $this->userId]),
        );
    }

    protected function getActions(): array
    {
        return [
            Action::make('cancel')
                ->label('キャンセル')
                ->color(function () {
                    return 'gray';
                })
                ->url(function () {
                    return SuspendedUser::getUrl([
                        'userId' => $this->userId,
                    ]);
                }),
            Action::make('update')
                ->label('アカウント一時停止解除')
                ->color(UserStatus::NORMAL->color())
                ->requiresConfirmation()
                ->action(fn() => $this->update())
        ];
    }
}
