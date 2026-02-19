<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\UsrOsPlatform;
use App\Domain\User\Models\UsrOsPlatformInterface;
use Illuminate\Support\Collection;

/**
 * APIをリクエストしたユーザー以外のOSプラットフォーム情報を取得するリポジトリ
 */
class UsrOsPlatformPublicRepository
{
    protected string $modelClass = UsrOsPlatform::class;

    public function create(string $usrUserId, string $osPlatform): UsrOsPlatformInterface
    {
        $usrOsPlatform = new UsrOsPlatform();
        $usrOsPlatform->usr_user_id = $usrUserId;
        $usrOsPlatform->os_platform = $osPlatform;
        // APIログインユーザー以外のユーザー用なのでsyncModelではなく直接saveを実行する
        $usrOsPlatform->save();

        return $usrOsPlatform;
    }

    public function getByUsrUserId(string $usrUserId): Collection
    {
        return UsrOsPlatform::query()->where('usr_user_id', $usrUserId)->get();
    }

    public function getByUsrUserIdAndOsPlatform(string $usrUserId, string $osPlatform): ?UsrOsPlatformInterface
    {
        return UsrOsPlatform::query()
            ->where('usr_user_id', $usrUserId)
            ->where('os_platform', $osPlatform)
            ->first();
    }
}
