<?php

declare(strict_types=1);

namespace App\Domain\Auth\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrDeviceEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

class UsrDevice extends UsrEloquentModel implements UsrDeviceInterface
{
    use HasFactory;

    // TODO: OS情報のカラムを追加
    protected $fillable = [
        'uuid',
        'usr_user_id',
        'bnid_linked_at',
        'os_platform',
    ];

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getBnidLinkedAt(): ?string
    {
        return $this->bnid_linked_at;
    }

    public function setBnidLinkedAt(?string $bnidLinkedAt): void
    {
        $this->bnid_linked_at = $bnidLinkedAt;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function toEntity(): UsrDeviceEntity
    {
        return new UsrDeviceEntity(
            $this->id,
            $this->usr_user_id,
            $this->uuid,
            $this->bnid_linked_at,
            $this->os_platform
        );
    }
}
