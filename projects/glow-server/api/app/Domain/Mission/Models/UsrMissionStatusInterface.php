<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrMissionStatusInterface extends UsrModelInterface
{
    public function isBeginnerMissionCompleted(): bool;

    public function isBeginnerMissionFullyUnlocked(): bool;

    public function getLatestMstHash(): string;

    public function setLatestMstHash(string $hash): void;

    public function getMissionUnlockedAt(): ?string;

    public function setMissionUnlockedAt(CarbonImmutable $now): void;

    public function setBeginnerMissionStatus(MissionBeginnerStatus $status): void;

    public function needInstantClear(string $currentMstHash): bool;
}
