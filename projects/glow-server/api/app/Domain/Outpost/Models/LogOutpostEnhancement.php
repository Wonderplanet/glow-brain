<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $mst_outpost_enhancement_id
 * @property int $before_level
 * @property int $after_level
 */
class LogOutpostEnhancement extends LogModel
{
    use HasFactory;

    public function setMstOutpostEnhancementId(string $mstOutpostEnhancementId): void
    {
        $this->mst_outpost_enhancement_id = $mstOutpostEnhancementId;
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
