<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstNgWordEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstNgWord extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'word' => 'string',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'word',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->word,
        );
    }
}
