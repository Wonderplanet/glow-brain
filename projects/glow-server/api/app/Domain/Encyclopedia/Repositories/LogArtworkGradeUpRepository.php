<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Repositories;

use App\Domain\Encyclopedia\Models\LogArtworkGradeUp;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogArtworkGradeUpRepository extends LogModelRepository
{
    protected string $modelClass = LogArtworkGradeUp::class;

    public function create(
        string $usrUserId,
        string $mstArtworkId,
        int $beforeGradeLevel,
        int $afterGradeLevel,
    ): LogArtworkGradeUp {
        $model = new LogArtworkGradeUp();
        $model->setUsrUserId($usrUserId);
        $model->setMstArtworkId($mstArtworkId);
        $model->setBeforeGradeLevel($beforeGradeLevel);
        $model->setAfterGradeLevel($afterGradeLevel);

        $this->addModel($model);

        return $model;
    }
}
