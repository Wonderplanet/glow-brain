<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngInGameNoticeEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MngInGameNotice extends MngModel
{
    use HasFactory;

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'display_type' => 'string',
        'mst_client_path_id' => 'string',
        'enable' => 'boolean',
        'priority' => 'integer',
        'display_frequency_type' => 'string',
        'destination_type' => 'string',
        'destination_path' => 'string',
        'destination_path_detail' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->display_type,
            $this->enable,
            $this->priority,
            $this->display_frequency_type,
            $this->destination_type,
            $this->destination_path,
            $this->destination_path_detail,
            $this->start_at,
            $this->end_at,
        );
    }
}
