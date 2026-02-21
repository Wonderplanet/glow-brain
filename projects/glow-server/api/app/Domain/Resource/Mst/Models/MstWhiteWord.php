<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstWhiteWordEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstWhiteWord extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'word' => 'string',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->word,
        );
    }
}
