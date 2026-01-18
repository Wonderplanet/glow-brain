<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\Models\UsrUserProfileInterface;

/**
 * APIをリクエストしたユーザー以外のユーザープロフィールを取得するリポジトリ
 */
class UsrUserProfilePublicRepository
{
    protected string $modelClass = UsrUserProfile::class;

    public function findByUsrUserId(string $usrUserId): UsrUserProfileInterface
    {
        return UsrUserProfile::query()
            ->where('usr_user_id', $usrUserId)
            ->first();
    }
}
