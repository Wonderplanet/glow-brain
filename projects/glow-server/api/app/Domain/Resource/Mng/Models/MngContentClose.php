<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngContentCloseEntity;
use App\Domain\Resource\Traits\HasFactory;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $content_type
 * @property ?string $content_id
 * @property CarbonImmutable $start_at
 * @property CarbonImmutable $end_at
 * @property int $is_valid
 */
class MngContentClose extends MngModel
{
    use HasFactory;

    protected $table = 'mng_content_closes';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'content_type' => 'string',
        'content_id' => 'string',
        'start_at' => 'immutable_datetime',
        'end_at' => 'immutable_datetime',
        'is_valid' => 'int',
    ];

    /**
     * ModelをEntityに変換
     */
    public function toEntity(): MngContentCloseEntity
    {
        return new MngContentCloseEntity(
            id: $this->id,
            content_type: $this->content_type,
            content_id: $this->content_id,
            start_at: $this->start_at,
            end_at: $this->end_at,
            is_valid: $this->is_valid
        );
    }
}
