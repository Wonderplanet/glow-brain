<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $mst_unit_id
 * @property int $before_rank
 * @property int $after_rank
 */
class LogUnitRankUp extends LogModel
{
    use HasFactory;

    public function setMstUnitId(string $mstUnitId): void
    {
        $this->mst_unit_id = $mstUnitId;
    }

    public function setBeforeRank(int $beforeRank): void
    {
        $this->before_rank = $beforeRank;
    }

    public function setAfterRank(int $afterRank): void
    {
        $this->after_rank = $afterRank;
    }
}
