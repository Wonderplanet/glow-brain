<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaRewardEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $unit_encyclopedia_rank
 * @property string $resource_type
 * @property string|null $resource_id
 * @property int $resource_amount
 * @property int $release_key
 */
class MstUnitEncyclopediaReward extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'unit_encyclopedia_rank' => 'integer',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'unit_encyclopedia_rank',
        'resource_type',
        'resource_id',
        'resource_amount',
        'release_key',
    ];

    public function toEntity(): MstUnitEncyclopediaRewardEntity
    {
        return new MstUnitEncyclopediaRewardEntity(
            $this->id,
            $this->unit_encyclopedia_rank,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->release_key,
        );
    }
}
