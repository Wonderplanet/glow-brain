<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstConfigEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstConfig extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'key' => 'string',
        'value' => 'string',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'release_key',
        'key',
        'value',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->key,
            $this->value,
        );
    }
}
