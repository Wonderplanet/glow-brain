<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstQuestEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $quest_type
 * @property string $mst_event_id
 * @property int $sort_order
 * @property string $asset_key
 * @property string $start_date
 * @property string $end_date
 * @property int $release_key
 */
class MstQuest extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'quest_type' => 'string',
        'mst_event_id' => 'string',
        'mst_series_id' => 'string',
        'sort_order' => 'integer',
        'asset_key' => 'string',
        'start_date' => 'string',
        'end_date' => 'string',
        'release_key' => 'integer',
        'quest_group' => 'string',
        'difficulty' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->quest_type,
            $this->mst_event_id,
            $this->mst_series_id,
            $this->sort_order,
            $this->asset_key,
            $this->start_date,
            $this->end_date,
            $this->release_key,
            $this->quest_group,
            $this->difficulty
        );
    }
}
