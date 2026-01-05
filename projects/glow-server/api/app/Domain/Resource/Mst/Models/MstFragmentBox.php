<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstFragmentBoxEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstFragmentBox extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_item_id' => 'string',
        'mst_fragment_box_group_id' => 'string',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'mst_item_id',
        'mst_fragment_box_group_id',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_item_id,
            $this->mst_fragment_box_group_id,
            $this->release_key,
        );
    }
}
