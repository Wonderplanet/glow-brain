<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitLevelUpEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstUnitLevelUp extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'unit_label' => 'string',
        'level' => 'integer',
        'required_coin' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'unit_label',
        'level',
        'required_coin',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->unit_label,
            $this->level,
            $this->required_coin,
        );
    }
}
