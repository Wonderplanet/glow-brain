<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Gacha\Enums\AppearanceCondition;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Resource\Mst\Entities\OprGachaEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property \Illuminate\Support\Carbon|null $start_at
 * @property \Illuminate\Support\Carbon|null $end_at
 */
class OprGacha extends MstModel
{
    use HasFactory;

    protected $table = "opr_gachas";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'gacha_type' => GachaType::class,
        'upper_group' => 'string',
        'enable_ad_play' => 'boolean',
        'enable_add_ad_play_upper' => 'boolean',
        'ad_play_interval_time' => 'integer',
        'multi_draw_count' => 'integer',
        'multi_fixed_prize_count' => 'integer',
        'daily_play_limit_count' => 'integer',
        'total_play_limit_count' => 'integer',
        'daily_ad_limit_count' => 'integer',
        'total_ad_limit_count' => 'integer',
        'prize_group_id' => 'string',
        'fixed_prize_group_id' => 'string',
        'appearance_condition' => AppearanceCondition::class,
        'unlock_condition_type' => 'string',
        'unlock_duration_hours' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'display_mst_unit_id' => 'string',
        'display_information_id' => 'string',
        'display_gacha_caution_id' => 'string',
        'gacha_priority' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->gacha_type,
            $this->upper_group,
            $this->enable_ad_play,
            $this->enable_add_ad_play_upper,
            $this->ad_play_interval_time,
            $this->multi_draw_count,
            $this->multi_fixed_prize_count,
            $this->daily_play_limit_count,
            $this->total_play_limit_count,
            $this->daily_ad_limit_count,
            $this->total_ad_limit_count,
            $this->prize_group_id,
            $this->fixed_prize_group_id,
            $this->appearance_condition,
            $this->unlock_condition_type,
            $this->unlock_duration_hours,
            $this->start_at,
            $this->end_at,
            $this->display_mst_unit_id
        );
    }
}
