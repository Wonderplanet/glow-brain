<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Repositories;

use App\Domain\Outpost\Models\LogOutpostEnhancement;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogOutpostEnhancementRepository extends LogModelRepository
{
    protected string $modelClass = LogOutpostEnhancement::class;

    public function create(
        string $usrUserId,
        string $mstOutpostEnhancementId,
        int $beforeLevel,
        int $afterLevel,
    ): LogOutpostEnhancement {
        $model = new LogOutpostEnhancement();
        $model->setUsrUserId($usrUserId);
        $model->setMstOutpostEnhancementId($mstOutpostEnhancementId);
        $model->setBeforeLevel($beforeLevel);
        $model->setAfterLevel($afterLevel);

        $this->addModel($model);

        return $model;
    }
}
