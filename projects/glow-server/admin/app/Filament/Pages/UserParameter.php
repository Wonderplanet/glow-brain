<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrUserParameter;

class UserParameter extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-parameter';

    public string $currentTab = UserSearchTabs::USER_PARAMETER->value;

    public array $userParameter;

    public function mount(): void
    {
        parent::mount();
        $this->setUserParameter();
        $this->breadcrumbList[self::getUrl()] = $this->currentTab;
    }

    private function setUserParameter(): void
    {
        $userParameter = UsrUserParameter::where('usr_user_id', $this->userId)->first();

        $this->userParameter = [
            'プレイヤーID' => $userParameter?->usr_user_id,
            'レベル' => $userParameter?->level,
            '経験値' => $userParameter?->exp,
            'コイン' => $userParameter?->coin,
            'スタミナ' => $userParameter?->stamina,
            'スタミナ更新日時' => $userParameter?->stamina_updated_at,
        ];
    }
}
