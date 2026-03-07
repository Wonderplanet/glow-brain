<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
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
        private readonly Clock $clock,
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
        // BANチェック
        $now = $this->clock->now();
        $this->userDelegator->checkUserBan($usrUserId, $now);
    }
}
