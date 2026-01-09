<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngDeletedMyIdEntity;
use App\Domain\Resource\Traits\HasFactory;

class MngDeletedMyId extends MngModel
{
    use HasFactory;

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'my_id' => 'string',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getMyId(): string
    {
        return $this->my_id;
    }

    public function toEntity(): MngDeletedMyIdEntity
    {
        return new MngDeletedMyIdEntity(
            $this->getId(),
            $this->getMyId(),
        );
    }
}
