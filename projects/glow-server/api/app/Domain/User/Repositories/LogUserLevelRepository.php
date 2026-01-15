<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\User\Models\LogUserLevel;

class LogUserLevelRepository extends LogModelRepository
{
    protected string $modelClass = LogUserLevel::class;

    public function create(
        string $usrUserId,
        int $beforeLevel,
        int $afterLevel
    ): LogUserLevel {
        $model = new LogUserLevel();
        $model->setUsrUserId($usrUserId);
        $model->setBeforeLevel($beforeLevel);
        $model->setAfterLevel($afterLevel);
        $this->addModel($model);
        return $model;
    }
}
