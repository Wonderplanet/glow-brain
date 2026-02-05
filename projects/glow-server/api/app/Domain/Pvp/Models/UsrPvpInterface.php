<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Models;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use App\Http\Responses\Data\OpponentPvpStatusData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface UsrPvpInterface extends UsrModelInterface
{
    public function getUsrUserId(): string;

    public function getSysPvpSeasonId(): string;

    public function getScore(): int;

    public function getMaxReceivedScoreReward(): int;

    public function receiveScoreReward(): void;

    public function getPvpRankClassType(): string;

    public function getPvpRankClassTypeEnum(): PvpRankClassType;

    public function getPvpRankClassLevel(): int;

    public function getRanking(): ?int;

    public function getDailyRemainingChallengeCount(): int;

    public function tryDecrementDailyRemainingChallengeCount(): bool;

    public function getDailyRemainingItemChallengeCount(): int;

    public function tryDecrementDailyRemainingItemChallengeCount(): bool;

    public function isExcludedRanking(): bool;

    public function setIsExcludedRanking(bool $isExcludedRanking): void;

    public function isSeasonRewardReceived(): bool;

    public function setIsSeasonRewardReceived(bool $isSeasonRewardReceived): void;

    public function getLastPlayedAt(): ?string;

    public function setLastPlayedAt(CarbonImmutable $now): void;

    public function getSelectedOpponentCandidates(): string;

    /** @return array<mixed> */
    public function getSelectedOpponentCandidatesToArray(): array;

    public function adjustScore(int $delta): void;

    public function setScore(int $score): void;

    public function resetRemainingChallengeCounts(
        int $remainingChallengeCount,
        int $remainingItemChallengeCount,
        CarbonImmutable $now
    ): void;

    public function updatePvpRankClass(PvpRankClassType $pvpRankClassType, int $pvpRankClassLevel): void;

    public function isPlayed(): bool;

    /**
     * @param Collection<OpponentPvpStatusData> $selectedOpponentCandidates
     */
    public function setSelectedOpponentCandidates(Collection $selectedOpponentCandidates): void;

    public function getLatestResetAt(): ?string;
}
