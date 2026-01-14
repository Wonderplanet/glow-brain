<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngClientVersionEntity;
use App\Domain\Resource\Traits\HasFactory;

class MngClientVersion extends MngModel
{
    use HasFactory;

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'client_version' => 'string',
        'platform' => 'integer',
        'is_force_update' => 'boolean',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getClientVersion(): string
    {
        return $this->client_version;
    }

    public function getPlatform(): int
    {
        return $this->platform;
    }

    public function isRequireUpdate(): bool
    {
        return (bool) $this->is_force_update;
    }

    public function toEntity(): MngClientVersionEntity
    {
        return new MngClientVersionEntity(
            $this->getId(),
            $this->getClientVersion(),
            $this->getPlatform(),
            $this->isRequireUpdate(),
        );
    }
}
