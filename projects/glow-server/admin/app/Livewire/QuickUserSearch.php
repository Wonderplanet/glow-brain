<?php

namespace App\Livewire;

use App\Filament\Pages\User\UserDetail;
use App\Models\Usr\UsrUserProfile;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Redirect;

class QuickUserSearch extends Widget
{
    protected static string $view = 'livewire.quick-user-search';

    public string $myId = '';
    public string $message = '';

    public function search()
    {
        $user = UsrUserProfile::query()
            ->where('my_id', $this->myId)
            ->first();

        if ($user) {
            return Redirect::to(UserDetail::getUrl(['userId' => $user->usr_user_id]));
        }

        $this->message = 'ユーザーが見つかりませんでした';
    }
}
