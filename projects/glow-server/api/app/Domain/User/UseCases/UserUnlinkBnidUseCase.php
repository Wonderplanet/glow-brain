<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Services\UserAccountLinkService;

class UserUnlinkBnidUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly UserAccountLinkService $userAccountLinkService,
    ) {
    }

    public function exec(CurrentUser $user, string $accessToken, string $platform): void
    {
        $this->applyUserTransactionChanges(function () use ($user, $accessToken, $platform) {
            $this->userAccountLinkService->unlinkBnid($user->id, $accessToken, $platform);
        });
    }
}
