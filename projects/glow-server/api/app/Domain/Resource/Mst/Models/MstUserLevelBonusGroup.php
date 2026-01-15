<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUserLevelBonusGroupEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstUserLevelBonusGroup extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_user_level_bonus_group_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'int',
        'release_key' => 'int',
    ];

    protected $fillable = [
        'id',
        'mst_user_level_bonus_group_id',
        'resource_type',
        'resource_id',
        'resource_amount',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_user_level_bonus_group_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
