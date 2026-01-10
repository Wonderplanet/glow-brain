<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Models;

use App\Domain\Resource\Usr\Entities\UsrOutpostEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrOutpostInterface extends UsrModelInterface
{
    public function getMstOutpostId(): string;
    public function setMstOutpostId(string $mstOutpostId): void;
    public function getMstArtworkId(): ?string;
    public function setMstArtworkId(?string $mstArtworkId): void;
    public function getIsUsed(): int;
    public function setIsUsed(int $isUsed): void;

    public function toEntity(): UsrOutpostEntity;
}
