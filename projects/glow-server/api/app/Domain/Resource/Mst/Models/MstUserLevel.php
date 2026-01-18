<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUserLevelEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstUserLevel extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'level' => 'int',
        'stamina' => 'int',
        'exp' => 'int',
        'release_key' => 'int',
    ];

    protected $fillable = [
        'id',
        'level',
        'stamina',
        'exp',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->level,
            $this->stamina,
            $this->exp,
        );
    }
}
