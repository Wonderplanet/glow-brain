<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $mst_unit_id
 * @property int $before_grade_level
 * @property int $after_grade_level
 */
class LogUnitGradeUp extends LogModel
{
    use HasFactory;

    public function setMstUnitId(string $mstUnitId): void
    {
        $this->mst_unit_id = $mstUnitId;
    }

    public function setBeforeGradeLevel(int $beforeGradeLevel): void
    {
        $this->before_grade_level = $beforeGradeLevel;
    }

    public function setAfterGradeLevel(int $afterGradeLevel): void
    {
        $this->after_grade_level = $afterGradeLevel;
    }
}
