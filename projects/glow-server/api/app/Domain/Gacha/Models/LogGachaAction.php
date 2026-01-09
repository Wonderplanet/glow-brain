<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $opr_gacha_id
 * @property string $cost_type
 * @property int $draw_count
 * @property int $max_rarity_upper_count
 * @property int $pickup_upper_count
 */
class LogGachaAction extends LogModel implements ILogGachaAction
{
    use HasFactory;

    public function setOprGachaId(string $oprGachaId): void
    {
        $this->opr_gacha_id = $oprGachaId;
    }

    public function setCostType(string $costType): void
    {
        $this->cost_type = $costType;
    }

    public function setDrawCount(int $drawCount): void
    {
        $this->draw_count = $drawCount;
    }

    public function setMaxRarityUpperCount(int $maxRarityUpperCount): void
    {
        $this->max_rarity_upper_count = $maxRarityUpperCount;
    }

    public function setPickupUpperCount(int $pickupUpperCount): void
    {
        $this->pickup_upper_count = $pickupUpperCount;
    }
}
