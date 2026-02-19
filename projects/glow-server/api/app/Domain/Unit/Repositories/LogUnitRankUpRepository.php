<?php

declare(strict_types=1);

namespace App\Domain\Unit\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Unit\Models\LogUnitRankUp;

class LogUnitRankUpRepository extends LogModelRepository
{
    protected string $modelClass = LogUnitRankUp::class;

    public function create(
        string $usrUserId,
        string $mstUnitId,
        int $beforeRank,
        int $afterRank,
    ): LogUnitRankUp {
        $model = new LogUnitRankUp();
        $model->setUsrUserId($usrUserId);
        $model->setMstUnitId($mstUnitId);
        $model->setBeforeRank($beforeRank);
        $model->setAfterRank($afterRank);

        $this->addModel($model);

        return $model;
    }
}
