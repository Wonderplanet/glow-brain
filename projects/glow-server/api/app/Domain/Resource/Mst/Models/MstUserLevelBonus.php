<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUserLevelBonusEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstUserLevelBonus extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'level' => 'int',
        'mst_user_level_bonus_group_id' => 'string',
        'release_key' => 'int',
    ];

    protected $fillable = [
        'id',
        'level',
        'mst_user_level_bonus_group_id',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->level,
            $this->mst_user_level_bonus_group_id,
        );
    }
}
