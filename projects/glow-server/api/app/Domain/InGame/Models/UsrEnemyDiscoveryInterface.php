<?php

declare(strict_types=1);

namespace App\Domain\InGame\Models;

use App\Domain\Resource\Usr\Entities\UsrEnemyDiscoveryEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrEnemyDiscoveryInterface extends UsrModelInterface
{
    public function getMstEnemyCharacterId(): string;
    public function getIsNewEncyclopedia(): int;
    public function markAsCollected(): void;
    public function isAlreadyCollected(): bool;
    public function toEntity(): UsrEnemyDiscoveryEntity;
}
