<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstDummyUserEntity as Entity;

class MstDummyUser extends MstModel
{
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'grade_unit_level_total_count' => 'integer',
        'mst_emblem_id' => 'string',
        'mst_unit_id' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_unit_id,
            $this->mst_emblem_id,
            $this->grade_unit_level_total_count,
        );
    }
}
