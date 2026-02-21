<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\RewardType;
use App\Domain\Resource\Mst\Models\OprGachaPrize as BaseOprGachaPrize;
use App\Dtos\RewardDto;
use App\Utils\StringUtil;

class OprGachaPrize extends BaseOprGachaPrize
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getPrizeResourceAttribute(): RewardDto
    {
        return new RewardDto(
            $this->id,
            $this->resource_type->value,
            $this->resource_id,
            $this->resource_amount,
        );
    }

    public function isUnit(): bool
    {
        return $this->resource_type->value === RewardType::UNIT->value
            && StringUtil::isSpecified($this->resource_id);
    }

    /**
     * ガシャシミュレーションで、前回のシミュレーションからマスタデータに変更があるかどうかを判定するためのデータを整形して返す
     * @return array<mixed>
     */
    public function formatToSimulationCheckData(): array
    {
        $entity = $this->toEntity();

        return [
            'id' => $entity->getId(),
            'group_id' => $entity->getGroupId(),
            'resource_type' => $entity->getResourceType()->value,
            'resource_id' => $entity->getResourceId(),
            'resource_amount' => $entity->getResourceAmount(),
            'weight' => $entity->getWeight(),
            'pickup' => $entity->getPickup(),
        ];
    }

    public function mst_unit()
    {
        return $this->belongsTo(MstUnit::class, 'resource_id');
    }
}
