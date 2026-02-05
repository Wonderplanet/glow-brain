<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $mst_artwork_id
 * @property int $before_grade_level
 * @property int $after_grade_level
 */
class LogArtworkGradeUp extends LogModel
{
    public function setMstArtworkId(string $mstArtworkId): void
    {
        $this->mst_artwork_id = $mstArtworkId;
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
