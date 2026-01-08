<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Models;

use App\Domain\Resource\Usr\Entities\UsrOutpostEnhancementEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrOutpostEnhancementInterface extends UsrModelInterface
{
    public function getMstOutpostId(): string;
    public function setMstOutpostId(string $mstOutpostId): void;

    public function getMstOutpostEnhancementId(): string;
    public function setMstOutpostEnhancementId(string $mstOutpostEnhancementId): void;

    public function getLevel(): int;
    public function setLevel(int $level): void;
    /**
     * @return array<mixed>
     */
    public function formatToLog(): array;

    public function toEntity(): UsrOutpostEnhancementEntity;
}
