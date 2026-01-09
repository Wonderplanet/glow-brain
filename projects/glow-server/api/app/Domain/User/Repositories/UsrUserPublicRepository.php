<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserInterface;

class UsrUserPublicRepository
{
    protected string $modelClass = UsrUser::class;

    public function getByBnUserId(string $bnUserId): ?UsrUserInterface
    {
        return UsrUser::query()
            ->where('bn_user_id', $bnUserId)
            ->first();
    }
}
