<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Services\UserService;

class UserChangeNameUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private UserService $userService,
    ) {
    }

    public function exec(CurrentUser $user, string $newName): void
    {
        $now = $this->clock->now();
        $this->userService->setNewName($user->id, $newName, $now);

        // トランザクション処理
        $this->applyUserTransactionChanges();
    }
}
