<?php

declare(strict_types=1);

namespace App\Domain\Unit\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Unit\Models\LogUnitGradeUp;

class LogUnitGradeUpRepository extends LogModelRepository
{
    protected string $modelClass = LogUnitGradeUp::class;

    public function create(
        string $usrUserId,
        string $mstUnitId,
        int $beforeGradeLevel,
        int $afterGradeLevel,
    ): LogUnitGradeUp {
        $model = new LogUnitGradeUp();
        $model->setUsrUserId($usrUserId);
        $model->setMstUnitId($mstUnitId);
        $model->setBeforeGradeLevel($beforeGradeLevel);
        $model->setAfterGradeLevel($afterGradeLevel);

        $this->addModel($model);

        return $model;
    }
}
