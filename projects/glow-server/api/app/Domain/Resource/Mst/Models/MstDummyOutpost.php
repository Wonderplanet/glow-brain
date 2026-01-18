<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstDummyOutpostEntity as Entity;

class MstDummyOutpost extends MstModel
{
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_dummy_user_id' => 'string',
        'mst_outpost_enhancement_id' => 'string',
        'level' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_dummy_user_id,
            $this->mst_outpost_enhancement_id,
            $this->level,
        );
    }
}
