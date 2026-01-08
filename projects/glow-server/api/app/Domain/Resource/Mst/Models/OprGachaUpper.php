<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Resource\Mst\Entities\OprGachaUpperEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string    $id
 * @property string    $upper_group
 * @property UpperType $upper_type
 * @property int       $count
 */
class OprGachaUpper extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = "opr_gacha_uppers";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'upper_group' => 'string',
        'upper_type' => UpperType::class,
        'count' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->upper_group,
            $this->upper_type,
            $this->count,
        );
    }
}
