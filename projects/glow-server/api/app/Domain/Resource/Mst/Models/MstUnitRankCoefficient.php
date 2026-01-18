<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitRankCoefficientEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $rank
 * @property int $coefficient
 * @property int $release_key
 */
class MstUnitRankCoefficient extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'rank' => 'integer',
        'coefficient' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->rank,
            $this->coefficient,
        );
    }
}
