<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Models;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrAdventBattleSessionInterface extends UsrModelInterface
{
    public function getMstAdventBattleId(): string;

    public function getIsValid(): AdventBattleSessionStatus;

    public function getPartyNo(): int;

    public function getBattleStartAt(): string;

    public function calcBattleTime(CarbonImmutable $now): int;

    public function isClosed(): bool;

    public function isStarted(): bool;

    public function isStartedByMstAdventBattleId(string $mstAdventBattleId): bool;

    public function closeSession(): void;

    public function startSession(
        string $mstAdventBattleId,
        int $partyNo,
        CarbonImmutable $now,
        bool $isChallengeAd
    ): void;

    public function isChallengeAd(): bool;
}
