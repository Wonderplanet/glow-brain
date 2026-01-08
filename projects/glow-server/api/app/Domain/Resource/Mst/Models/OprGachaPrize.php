<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $group_id
 * @property RewardType $resource_type
 * @property null|string $resource_id
 * @property int $resource_amount
 * @property int $weight
 * @property bool $pickup
 * @property int $release_key
 */
class OprGachaPrize extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = "opr_gacha_prizes";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'group_id' => 'string',
        'resource_type' => RewardType::class,
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'weight' => 'integer',
        'pickup' => 'bool',
        'release_key' => 'integer',
    ];

    public function toEntity(): OprGachaPrizeEntity
    {
        return new OprGachaPrizeEntity(
            $this->id,
            $this->group_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->weight,
            $this->pickup,
        );
    }
}
