<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories\Contracts;

use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

interface MstMissionRepositoryReceiveRewardInterface
{
    public function getById(string $id, bool $isThrowError = false): ?MstMissionEntityReceiveRewardInterface;
}
