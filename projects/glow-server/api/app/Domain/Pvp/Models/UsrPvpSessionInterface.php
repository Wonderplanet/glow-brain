<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Models;

use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use App\Http\Responses\Data\OpponentPvpStatusData;
use Carbon\CarbonImmutable;

interface UsrPvpSessionInterface extends UsrModelInterface
{
    public function getUsrUserId(): string;

    public function getSysPvpSeasonId(): string;

    public function getOpponentScore(): int;

    public function getPartyNo(): int;

    public function getOpponentMyId(): ?string;

    public function getOpponentPvpStatus(): string;

    /** @return array<mixed> */
    public function getOpponentPvpStatusToArray(): array;

    public function getIsValid(): PvpSessionStatus;

    public function isClosed(): bool;

    public function isStarted(): bool;

    public function closeSession(): void;

    public function startSession(
        string $sysPvpSeasonId,
        int $partyNo,
        string $opponentMyId,
        OpponentPvpStatusData $opponentPvpStatusData,
        int $opponentScore,
        CarbonImmutable $now,
        bool $isUseItem
    ): void;

    public function calcBattleTime(CarbonImmutable $now): int;

    public function getIsUseItem(): int;

    public function isUseItem(): bool;

    public function getBattleStartAtAsCarbon(): CarbonImmutable;
}
