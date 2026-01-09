<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $mst_unit_id
 * @property int $before_level
 * @property int $after_level
 */
class LogUnitLevelUp extends LogModel
{
    use HasFactory;

    public function setMstUnitId(string $mstUnitId): void
    {
        $this->mst_unit_id = $mstUnitId;
    }

    public function setBeforeLevel(int $beforeLevel): void
    {
        $this->before_level = $beforeLevel;
    }

    public function setAfterLevel(int $afterLevel): void
    {
        $this->after_level = $afterLevel;
    }
}
