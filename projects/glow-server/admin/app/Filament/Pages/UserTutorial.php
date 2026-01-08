<?php

namespace App\Filament\Pages;

use App\Constants\TutorialFunctionName;
use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrIdleIncentive;
use App\Models\Usr\UsrUser;
use App\Utils\StringUtil;

class UserTutorial extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-tutorial';

    public string $currentTab = UserSearchTabs::TUTORIAL->value;

    public array $userTutorial = [];

    public function mount()
    {
        parent::mount();

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);

        $this->setUserIdleIncentive();
    }

    private function setUserIdleIncentive(): void
    {
        $usrUser = UsrUser::find($this->userId);

        if (empty($usrUser)) {
            return;
        }

        $tutorialStatus = $usrUser->tutorial_status;
        if (StringUtil::isNotSpecified($tutorialStatus)) {
            $tutorialStatus = TutorialFunctionName::NOT_PLAYED->label();
        }

        $this->userTutorial = [
            'メインパートまでの進捗状況' => $tutorialStatus,
        ];
    }
}
