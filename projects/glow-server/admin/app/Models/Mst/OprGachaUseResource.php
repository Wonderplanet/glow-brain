<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\OprGachaUseResource as BaseOprGachaUseResource;
use App\Dtos\RewardDto;

class OprGachaUseResource extends BaseOprGachaUseResource
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getCostResourceAttribute(): RewardDto
    {
        return new RewardDto(
            $this->id,
            $this->cost_type->value,
            $this->cost_id,
            $this->cost_num,
        );
    }
}
