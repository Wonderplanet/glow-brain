<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitGradeUpRewardEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstUnitGradeUpReward extends MstModel
{
    use HasFactory;

    public $timestamps = true;

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_unit_id' => 'string',
        'grade_level' => 'integer',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'release_key',
        'mst_unit_id',
        'grade_level',
        'resource_type',
        'resource_id',
        'resource_amount',
    ];

    public function toEntity(): MstUnitGradeUpRewardEntity
    {
        return new MstUnitGradeUpRewardEntity(
            $this->id,
            $this->mst_unit_id,
            $this->grade_level,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->release_key,
        );
    }
}
