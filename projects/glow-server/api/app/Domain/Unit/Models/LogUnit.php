<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $mst_unit_id
 * @property int $level
 * @property int $rank
 * @property int $grade_level
 * @property string $trigger_source
 * @property string $trigger_value
 * @property string $trigger_value_2
 * @property string $trigger_value_3
 */
class LogUnit extends LogModel
{
    public function setMstUnitId(string $mstUnitId): void
    {
        $this->mst_unit_id = $mstUnitId;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    public function setGradeLevel(int $gradeLevel): void
    {
        $this->grade_level = $gradeLevel;
    }

    public function setTriggerSource(string $triggerSource): void
    {
        $this->trigger_source = $triggerSource;
    }

    public function setTriggerValue(string $triggerValue): void
    {
        $this->trigger_value = $triggerValue;
    }

    public function setTriggerValue2(string $triggerValue2): void
    {
        $this->trigger_value_2 = $triggerValue2;
    }

    public function setTriggerValue3(string $triggerValue3): void
    {
        $this->trigger_value_3 = $triggerValue3;
    }
}
