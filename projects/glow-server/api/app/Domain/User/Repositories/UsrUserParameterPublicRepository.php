<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserParameterInterface;

/**
 * APIをリクエストしたユーザー以外のユーザーパラメーターを取得するリポジトリ
 */
class UsrUserParameterPublicRepository
{
    protected string $modelClass = UsrUserParameter::class;

    public function findByUsrUserId(string $usrUserId): UsrUserParameterInterface
    {
        return UsrUserParameter::query()
            ->where('usr_user_id', $usrUserId)
            ->first();
    }
}
