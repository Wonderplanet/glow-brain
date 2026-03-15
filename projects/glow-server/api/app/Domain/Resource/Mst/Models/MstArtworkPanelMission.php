<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkPanelMissionEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_id
 * @property string $mst_event_id
 * @property string|null $initial_open_mst_artwork_fragment_id
 * @property string $start_at
 * @property string $end_at
 */
class MstArtworkPanelMission extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_id' => 'string',
        'mst_event_id' => 'string',
        'initial_open_mst_artwork_fragment_id' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_artwork_id,
            $this->mst_event_id,
            $this->initial_open_mst_artwork_fragment_id,
            $this->start_at,
            $this->end_at,
        );
    }
}
