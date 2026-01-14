<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstDummyUserUnitEntity as Entity;

class MstDummyUserUnit extends MstModel
{
    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_unit_id,
            $this->mst_dummy_user_id,
            $this->level,
            $this->rank,
            $this->grade_level
        );
    }

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_unit_id' => 'string',
        'level' => 'integer',
        'rank' => 'integer',
        'grade_level' => 'integer',
    ];
}
