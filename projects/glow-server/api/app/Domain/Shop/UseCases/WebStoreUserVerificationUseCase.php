<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\User\Delegators\UserDelegator;

/**
 * WebStore W3: ユーザー検証
 *
 * notification_type: user_validation
 */
class WebStoreUserVerificationUseCase
{
    public function __construct(
        private readonly UserDelegator $userDelegator,
    ) {
    }

    /**
     * W3: ユーザー検証
     *
     * @param string $usrUserId ユーザーID
     * @return void
     */
    public function exec(string $usrUserId): void
    {
        // findById()はユーザーが存在しない場合、GameException(USER_NOT_FOUND)をthrowする
        $this->userDelegator->getUsrUserByUsrUserId($usrUserId);
    }
}
