<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstEventEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_series_id
 * @property int $is_displayed_series_logo
 * @property int $is_displayed_jump_plus
 * @property string $start_at
 * @property string $end_at
 * @property string $asset_key
 * @property int $release_key
 */
class MstEvent extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_series_id' => 'string',
        'is_displayed_series_logo' => 'integer',
        'is_displayed_jump_plus' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
        'asset_key' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_series_id,
            $this->is_displayed_series_logo,
            $this->is_displayed_jump_plus,
            $this->start_at,
            $this->end_at,
            $this->asset_key,
            $this->release_key,
        );
    }
}
