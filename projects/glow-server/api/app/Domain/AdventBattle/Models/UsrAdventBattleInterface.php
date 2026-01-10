<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrAdventBattleInterface extends UsrModelInterface
{
    public function getMstAdventBattleId(): string;

    public function getMaxScore(): int;

    public function setMaxScore(int $score): void;

    public function getTotalScore(): int;

    public function setTotalScore(int $score): void;

    public function getChallengeCount(): int;

    public function setChallengeCount(int $count): void;

    public function getResetChallengeCount(): int;

    public function setResetChallengeCount(int $count): void;

    public function getResetAdChallengeCount(): int;

    public function setResetAdChallengeCount(int $count): void;

    public function getClearCount(): int;

    public function setClearCount(int $count): void;

    public function getMaxReceivedMaxScoreReward(): int;

    public function receiveMaxScoreReward(): void;

    public function setMaxReceivedMaxScoreReward(int $maxScore): void;

    public function getReceivedRankRewardGroupId(): ?string;

    public function setReceivedRankRewardGroupId(string $groupId): void;

    public function getReceivedRaidRewardGroupId(): ?string;

    public function setReceivedRaidRewardGroupId(string $groupId): void;

    public function setIsRankingRewardReceived(bool $isReceived): void;


    public function getMaxScoreParty(): ?string;

    /**
     * @return array<mixed>
     */
    public function getMaxScorePartyArray(): array;

    /**
     * @param array<mixed> $party
     */
    public function setMaxScoreParty(array $party): void;

    public function isRankingRewardReceived(): bool;

    public function isExcludedRanking(): bool;

    public function setIsExcludedRanking(bool $isExcludedRanking): void;

    public function getLatestResetAt(): ?string;

    public function incrementChallengeCount(bool $isChallengeAd): void;

    public function decrementChallengeCount(bool $isChallengeAd): void;

    public function incrementClearCount(): void;

    public function isFirstClear(): bool;

    public function reset(CarbonImmutable $now): void;
}
