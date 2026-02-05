<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Repositories;

use App\Domain\Gacha\Models\LogGachaAction;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogGachaActionRepository extends LogModelRepository
{
    protected string $modelClass = LogGachaAction::class;

    public function create(
        string $usrUserId,
        string $oprGachaId,
        string $costType,
        int $drawCount,
        int $maxRarityUpperCount,
        int $pickupUpperCount,
    ): LogGachaAction {
        $model = new LogGachaAction();
        $model->setUsrUserId($usrUserId);

        $model->setOprGachaId($oprGachaId);
        $model->setCostType($costType);
        $model->setDrawCount($drawCount);
        $model->setMaxRarityUpperCount($maxRarityUpperCount);
        $model->setPickupUpperCount($pickupUpperCount);

        $this->addModel($model);

        return $model;
    }
}
