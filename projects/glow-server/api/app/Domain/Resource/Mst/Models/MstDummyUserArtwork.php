<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstDummyUserArtworkEntity as Entity;

class MstDummyUserArtwork extends MstModel
{
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_dummy_user_id' => 'string',
        'mst_artwork_id' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_dummy_user_id,
            $this->mst_artwork_id,
        );
    }
}
