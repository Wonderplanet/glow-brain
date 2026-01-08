<?php

declare(strict_types=1);

namespace App\Domain\Unit\Repositories;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\Contracts\ILogModelRepositoryRewardSend;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Unit\Constants\UnitConstant;
use App\Domain\Unit\Models\LogUnit;

class LogUnitRepository extends LogModelRepository implements ILogModelRepositoryRewardSend
{
    protected string $modelClass = LogUnit::class;

    public function create(
        string $usrUserId,
        string $mstUnitId,
        int $level,
        int $rank,
        int $gradeLevel,
        string $triggerSource,
        string $triggerValue,
        string $triggerValue2,
        string $triggerValue3
    ): LogUnit {
        $model = new LogUnit();
        $model->setUsrUserId($usrUserId);
        $model->setMstUnitId($mstUnitId);
        $model->setLevel($level);
        $model->setRank($rank);
        $model->setGradeLevel($gradeLevel);
        $model->setTriggerSource($triggerSource);
        $model->setTriggerValue($triggerValue);
        $model->setTriggerValue2($triggerValue2);
        $model->setTriggerValue3($triggerValue3);

        $this->addModel($model);

        return $model;
    }

    /**
     * 報酬として獲得したログデータを作成する
     */
    public function createByReward(string $usrUserId, BaseReward $reward): void
    {
        $logTriggerDto = $reward->getLogTriggerData();
        $this->create(
            $usrUserId,
            $reward->getResourceId(),
            UnitConstant::FIRST_UNIT_LEVEL,
            UnitConstant::FIRST_UNIT_RANK,
            UnitConstant::FIRST_UNIT_GRADE_LEVEL,
            $logTriggerDto->getTriggerSource(),
            $logTriggerDto->getTriggerValue(),
            '',
            '',
        );
    }
}
