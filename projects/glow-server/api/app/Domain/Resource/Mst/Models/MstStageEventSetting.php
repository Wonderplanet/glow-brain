<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstStageEventSettingEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_stage_id
 * @property string $reset_type
 * @property int $clearable_count
 * @property int $ad_challenge_count
 * @property string|null $background_asset_key
 * @property string $mst_stage_rule_group_id
 * @property string $start_at
 * @property string $end_at
 * @property int $release_key
 */
class MstStageEventSetting extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_stage_id' => 'string',
        'reset_type' => 'string',
        'clearable_count' => 'integer',
        'ad_challenge_count' => 'integer',
        'background_asset_key' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_stage_id,
            $this->reset_type,
            $this->clearable_count,
            $this->ad_challenge_count,
            $this->background_asset_key,
            $this->start_at,
            $this->end_at,
            $this->release_key
        );
    }
}
