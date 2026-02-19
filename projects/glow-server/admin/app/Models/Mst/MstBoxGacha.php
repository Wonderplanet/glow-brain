<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\RewardType;
use App\Domain\Resource\Mst\Models\MstBoxGacha as BaseMstBoxGacha;
use App\Dtos\RewardDto;
use Illuminate\Support\Collection;

class MstBoxGacha extends BaseMstBoxGacha
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    /**
     * コストアイテム情報をRewardDtoのCollectionとして取得
     *
     * @return Collection<int, RewardDto>
     */
    public function getCostDtos(): Collection
    {
        $result = collect();

        if (empty($this->cost_id)) {
            return $result;
        }

        $result->push(
            new RewardDto(
                $this->cost_id,
                RewardType::ITEM->value,
                $this->cost_id,
                $this->cost_num,
            )
        );

        return $result;
    }

    public function mst_event()
    {
        return $this->hasOne(MstEvent::class, 'id', 'mst_event_id');
    }

    public function mst_box_gacha_groups()
    {
        return $this->hasMany(MstBoxGachaGroup::class, 'mst_box_gacha_id', 'id')
            ->orderBy('box_level', 'asc');
    }

    public function getEventName(): string
    {
        return $this->mst_event?->getName() ?? '';
    }
}
