<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstCheatSettingEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $content_type
 * @property string $cheat_type
 * @property int $cheat_value
 * @property int $is_excluded_ranking
 * @property string $start_at
 * @property string $end_at
 * @property int $release_key
 */
class MstCheatSetting extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'content_type' => 'string',
        'cheat_type' => 'string',
        'cheat_value' => 'integer',
        'is_excluded_ranking' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->content_type,
            $this->cheat_type,
            $this->cheat_value,
            $this->is_excluded_ranking,
            $this->start_at,
            $this->end_at,
            $this->release_key,
        );
    }
}
