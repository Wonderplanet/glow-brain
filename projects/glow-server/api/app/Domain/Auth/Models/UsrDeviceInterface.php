<?php

declare(strict_types=1);

namespace App\Domain\Auth\Models;

use App\Domain\Resource\Usr\Entities\UsrDeviceEntity;

interface UsrDeviceInterface
{
    public function getId(): string;

    public function getUsrUserId(): string;

    public function getUuid(): string;

    public function getBnidLinkedAt(): ?string;

    public function setBnidLinkedAt(?string $bnidLinkedAt): void;

    public function toEntity(): UsrDeviceEntity;
}
