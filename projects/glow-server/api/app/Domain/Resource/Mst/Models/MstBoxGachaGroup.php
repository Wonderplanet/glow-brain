<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstBoxGachaGroupEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_box_gacha_id
 * @property int $box_level
 */
class MstBoxGachaGroup extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_box_gacha_id' => 'string',
        'box_level' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_box_gacha_id,
            $this->box_level,
        );
    }
}
