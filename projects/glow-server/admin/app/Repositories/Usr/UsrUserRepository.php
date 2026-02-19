<?php

declare(strict_types=1);

namespace App\Repositories\Usr;

use App\Models\Usr\UsrUser;
use Illuminate\Support\Collection;

class UsrUserRepository
{
    public function getBnUserIdsByUserIds(Collection $userIds): Collection
    {
        return UsrUser::query()
            ->whereIn('id', $userIds)
            ->pluck('bn_user_id', 'id');
    }
}
