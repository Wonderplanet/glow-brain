<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\User\Models\UsrUserProfile;

class ResetNameUpdateAtUseCase extends BaseCommands
{
    protected string $name = 'ユーザー名更新時間をリセット';
    protected string $description = 'ユーザー名更新時間をリセットして、再度ユーザー名変更ができるようにします';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: ユーザー名更新時間をリセット
     *
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        UsrUserProfile::query()
            ->where('usr_user_id', $user->getId())
            ->update(['name_update_at' => null]);
    }
}
