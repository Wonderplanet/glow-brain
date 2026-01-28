<?php

declare(strict_types=1);

namespace App\Domain\Unit\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Unit\Models\LogUnitLevelUp;

class LogUnitLevelUpRepository extends LogModelRepository
{
    protected string $modelClass = LogUnitLevelUp::class;

    public function create(
        string $usrUserId,
        string $mstUnitId,
        int $beforeLevel,
        int $afterLevel,
    ): LogUnitLevelUp {
        $model = new LogUnitLevelUp();
        $model->setUsrUserId($usrUserId);
        $model->setMstUnitId($mstUnitId);
        $model->setBeforeLevel($beforeLevel);
        $model->setAfterLevel($afterLevel);

        $this->addModel($model);

        return $model;
    }
}
