<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstIdleIncentiveEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstIdleIncentive extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'asset_key' => 'string',
        'initial_reward_receive_minutes' => 'integer',
        'reward_increase_interval_minutes' => 'integer',
        'max_idle_hours' => 'integer',
        'max_daily_diamond_quick_receive_amount' => 'integer',
        'required_quick_receive_diamond_amount' => 'integer',
        'max_daily_ad_quick_receive_amount' => 'integer',
        'ad_interval_seconds' => 'integer',
        'quick_idle_minutes' => 'integer',
    ];

    protected $fillable = [
        'id',
        'release_key',
        'asset_key',
        'initial_reward_receive_minutes',
        'reward_increase_interval_minutes',
        'max_idle_hours',
        'max_daily_diamond_quick_receive_amount',
        'required_quick_receive_diamond_amount',
        'max_daily_ad_quick_receive_amount',
        'ad_interval_seconds',
        'quick_idle_minutes',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->asset_key,
            $this->initial_reward_receive_minutes,
            $this->reward_increase_interval_minutes,
            $this->max_idle_hours,
            $this->max_daily_diamond_quick_receive_amount,
            $this->required_quick_receive_diamond_amount,
            $this->max_daily_ad_quick_receive_amount,
            $this->ad_interval_seconds,
            $this->quick_idle_minutes,
        );
    }
}
