<?php

namespace App\Filament\Pages;

use App\Constants\UserStatus;
use App\Filament\Pages\SuspendedUser\SuspendedUserSearch;
use App\Models\Adm\AdmUserBanOperateHistory;
use App\Models\Usr\UsrUser;
use App\Traits\NotificationTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class EditSuspendedUser extends Page
{
    use NotificationTrait;

    protected static string $view = 'filament.pages.edit-suspended-user';
    protected static ?string $title = 'アカウント停止';
    protected static bool $shouldRegisterNavigation = false;

    public string $userId = '';
    public int $status = 0;

    protected $queryString = [
        'userId',
        'status'
    ];

    public int $reasonForSuspension;
    public int $suspendPeriod;
    public string $operationReason;

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            SuspendedUserSearch::getUrl() => 'アカウント停止',
            SuspendedUser::getUrl(['userId' => $this->userId]) => 'アカウント停止詳細',
            self::getUrl(['userId' => $this->userId, 'status' => $this->status]) => 'アカウント一時停止',
        ]);
        $this->setTitle();
    }

    private function setTitle()
    {
        return $this::$title = 'アカウント一時停止';
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('reasonForSuspension')
                ->label('アカウント一時停止理由')
                ->options(
                    UserStatus::getSuspensionReasons(),
                )
                ->required()
                ->live()
                ->reactive(),
            Select::make('suspendPeriod')
                ->label('アカウント一時停止期間')
                ->options(function () {
                    return UserStatus::getBanTemporarySuspendPeriodDayOptions();
                })
                ->placeholder('--- 一時停止期間を選択してください ---')
                ->visible(function (callable $get) {
                    $status = $get('reasonForSuspension');
                    return UserStatus::isBanTemporaryStatus($status);
                })
                ->required()
                ->reactive(),
            Textarea::make('operationReason')
                ->label('アカウント一時停止経緯')
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

        $status = $this->form->getState()['reasonForSuspension'];
        $suspendPeriod = null;
        if (UserStatus::isBanTemporaryStatus($status)) {
            $suspendPeriod = $now->addDays($this->form->getState()['suspendPeriod'])
                ->setTime(23, 59, 59)
                ->format('Y-m-d H:i:s');
        }

        UsrUser::query()
            ->where('id', $this->userId)
            ->update([
                'status' => $status,
                'suspend_end_at' => $suspendPeriod,
            ]);

        AdmUserBanOperateHistory::query()->insert([
            'id' => Str::uuid(),
            'usr_user_id' => $this->userId,
            'ban_status' => $status,
            'adm_user_id' => auth()->id(),
            'operation_reason' => $this->form->getState()['operationReason'],
            'operated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->sendProcessCompletedNotification(
            'アカウントを一時停止にしました。',
            "ステータス: " . UserStatus::tryFrom($status)?->label() ?? '',
        );

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
                ->label('アカウント一時停止')
                ->color(function () {
                    return 'warning';
                })
                ->requiresConfirmation()
                ->action(fn () => $this->update()),
        ];
    }
}
