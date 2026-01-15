<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstFragmentBoxGroupEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstFragmentBoxGroup extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_fragment_box_group_id' => 'string',
        'mst_item_id' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'mst_fragment_box_group_id',
        'mst_item_id',
        'start_at',
        'end_at',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_fragment_box_group_id,
            $this->mst_item_id,
            $this->start_at,
            $this->end_at,
            $this->release_key,
        );
    }
}
