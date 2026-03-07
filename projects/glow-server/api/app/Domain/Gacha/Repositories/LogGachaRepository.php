<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Repositories;

use App\Domain\Gacha\Models\LogGacha;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogGachaRepository extends LogModelRepository
{
    protected string $modelClass = LogGacha::class;

    /**
     * @param string $usrUserId
     * @param string $oprGachaId
     * @param array<mixed>  $result
     * @param string $costType
     * @param int    $drawCount
     * @param int|null $stepNumber
     */
    public function create(
        string $usrUserId,
        string $oprGachaId,
        array $result,
        string $costType,
        int $drawCount,
        ?int $stepNumber = null,
    ): void {
        $model = new LogGacha();

        $model->setUsrUserId($usrUserId);
        $model->setOprGachaId($oprGachaId);
        $model->setResult($result);
        $model->setCostType($costType);
        $model->setDrawCount($drawCount);
        $model->setStepNumber($stepNumber);

        $this->addModel($model);
    }
}
