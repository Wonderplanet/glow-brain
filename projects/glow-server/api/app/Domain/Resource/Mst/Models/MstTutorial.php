<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstTutorialEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstTutorial extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'type' => 'string',
        'sort_order' => 'integer',
        'function_name' => 'string',
        'condition_type' => 'string',
        'condition_value' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->type,
            $this->sort_order,
            $this->function_name,
            $this->condition_type,
            $this->condition_value,
            $this->start_at,
            $this->end_at,
        );
    }
}
