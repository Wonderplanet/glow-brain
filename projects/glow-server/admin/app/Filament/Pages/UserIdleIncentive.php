<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrIdleIncentive;

class UserIdleIncentive extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-idle-incentive';

    public string $currentTab = UserSearchTabs::IDLE_INCENTIVE->value;

    public array $userIdleIncentive = [];

    public function mount()
    {
        parent::mount();
        $this->setUserIdleIncentive();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function setUserIdleIncentive(): void
    {
        $userIdleIncentive = UsrIdleIncentive::where('usr_user_id', $this->userId)->first();

        if (empty($userIdleIncentive)) {
            return;
        }

        $this->userIdleIncentive = [
            '探索開始日時' => $userIdleIncentive->idle_started_at,
            'プリズムクイック探索回数' => $userIdleIncentive->diamond_quick_receive_count,
            'プリズムクイック探索獲得日時' => $userIdleIncentive->diamond_quick_receive_at,
            '動画広告クイック探索回数' => $userIdleIncentive->ad_quick_receive_count,
            '動画広告クイック探索獲得日時' => $userIdleIncentive->ad_quick_receive_at,
        ];
    }
}
