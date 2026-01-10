<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Services\UserService;
use App\Http\Responses\ResultData\UserChangeEmblemResultData;

class UserChangeEmblemUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UserService $userService,
    ) {
    }

    /**
     * エンブレムを登録する
     * @param CurrentUser $user
     * @param string|null $mstEmblemId
     * @return UserChangeEmblemResultData
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function exec(CurrentUser $user, ?string $mstEmblemId): UserChangeEmblemResultData
    {
        $usrUserProfile = $this->userService->setNewEmblem($user->id, $mstEmblemId);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        // レスポンス作成
        return new UserChangeEmblemResultData($usrUserProfile);
    }
}
