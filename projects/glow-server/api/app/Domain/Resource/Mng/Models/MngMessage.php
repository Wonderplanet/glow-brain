<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngMessageEntity as Entity;

/**
 * @property string $id
 * @property string $start_at
 * @property string $expired_at
 * @property string $type
 * @property string $account_created_start_at
 * @property string $account_created_end_at
 * @property int $add_expired_days
 */
class MngMessage extends MngModel
{
    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'start_at' => 'string',
        'expired_at' => 'string',
        'type' => 'string',
        'account_created_start_at' => 'string',
        'account_created_end_at' => 'string',
        'add_expired_days' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->start_at,
            $this->expired_at,
            $this->type,
            $this->account_created_start_at,
            $this->account_created_end_at,
            $this->add_expired_days,
        );
    }
}
