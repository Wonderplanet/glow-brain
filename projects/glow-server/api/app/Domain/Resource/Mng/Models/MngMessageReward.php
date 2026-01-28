<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngMessageRewardEntity as Entity;

/**
 * @property string $id
 * @property string $mng_message_id
 * @property integer $display_order
 * @property string $resource_type
 * @property string|null $resource_id
 * @property integer|null $resource_amount
 */
class MngMessageReward extends MngModel
{
    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'mng_message_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mng_message_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->display_order,
        );
    }
}
