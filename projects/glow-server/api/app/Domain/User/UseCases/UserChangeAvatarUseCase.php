<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Services\UserService;
use App\Http\Responses\ResultData\UserChangeAvatarResultData;

class UserChangeAvatarUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UserService $userService,
    ) {
    }

    /**
     * リーダーアバターを登録する
     * @param CurrentUser $user
     * @param string $mstUnitId
     * @return UserChangeAvatarResultData
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function exec(CurrentUser $user, string $mstUnitId): UserChangeAvatarResultData
    {
        $usrUserProfile = $this->userService->setNewAvatar($user->id, $mstUnitId);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        // レスポンス作成
        return new UserChangeAvatarResultData($usrUserProfile);
    }
}
