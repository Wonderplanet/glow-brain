<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Entities;

use App\Domain\Resource\Entities\InGameBattleLog;
use Illuminate\Support\Collection;

class PvpInGameBattleLog extends InGameBattleLog
{
    /**
     * @param int $clearTimeMs
     * @param int $maxDamage
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $partyStatus
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $opponentPartyStatus
     */
    public function __construct(
        private readonly int $clearTimeMs,
        private readonly int $maxDamage,
        private readonly Collection $partyStatus,
        private readonly Collection $opponentPartyStatus,
    ) {
    }

    public function getClearTimeMs(): int
    {
        return $this->clearTimeMs;
    }

    public function getMaxDamage(): int
    {
        return $this->maxDamage;
    }

    public function getPartyStatus(): Collection
    {
        return $this->partyStatus;
    }

    public function getOpponentPartyStatus(): Collection
    {
        return $this->opponentPartyStatus;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'clear_time_ms' => $this->clearTimeMs,
            'max_damage' => $this->maxDamage,
            'party_status' => $this->partyStatus->map(fn($status) => $status->formatToLog())->toArray(),
            'opponent_party_status' => $this->opponentPartyStatus
                ->map(fn($status) => $status->formatToLog())->toArray(),
        ];
    }
}
