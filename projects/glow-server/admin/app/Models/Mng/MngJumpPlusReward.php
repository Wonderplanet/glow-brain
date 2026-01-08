<?php

namespace App\Models\Mng;

use App\Constants\Database;
use App\Domain\Resource\Mng\Models\MngJumpPlusReward as BaseMngJumpPlusReward;
use App\Dtos\RewardDto;
use Carbon\CarbonImmutable;

class MngJumpPlusReward extends BaseMngJumpPlusReward
{
    protected $connection = Database::MANAGE_DATA_CONNECTION;

    protected $guarded = [];

    public function getRewardAttribute(): RewardDto
    {
        return new RewardDto(
            $this->id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }

    public function formatToResponse(): array
    {
        return parent::toArray();
    }

    public static function createFromResponseArray(array $response): self
    {
        $model = new self();
        $model->fill($response);
        return $model;
    }

    public function formatToInsertArray(): array
    {
        $array = $this->toArray();

        $now = CarbonImmutable::now();
        $array['created_at'] = $now;
        $array['updated_at'] = $now;

        return $array;
    }
}
