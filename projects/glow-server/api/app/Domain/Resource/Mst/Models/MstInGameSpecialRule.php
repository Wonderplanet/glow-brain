<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstInGameSpecialRuleEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstInGameSpecialRule extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'content_type' => 'string',
        'target_id' => 'string',
        'rule_type' => 'string',
        'rule_value' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->content_type,
            $this->target_id,
            $this->rule_type,
            $this->rule_value,
            $this->start_at,
            $this->end_at
        );
    }
}
