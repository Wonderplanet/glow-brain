<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use App\Domain\Stage\Enums\StageSessionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface UsrStageSessionInterface extends UsrModelInterface
{
    public function getMstStageId(): string;

    public function getIsValid(): StageSessionStatus;

    public function getPartyNo(): int;

    public function getContinueCount(): int;

    public function getDailyContinueAdCount(): int;

    public function getOprCampaignIds(): Collection;

    public function isStarted(): bool;

    public function isClosed(): bool;

    public function isStartedByMstStageId(string $mstStageId): bool;

    public function closeSession(): void;

    public function startSession(
        string $mstStageId,
        int $partyNo,
        Collection $oprCampaignIds,
        bool $isChallengeAd,
        int $lapCount,
    ): void;

    public function incrementContinueCount(): void;

    public function incrementDailyContinueAdCount(): void;

    public function getLatestResetAt(): string;

    public function resetDaily(CarbonImmutable $now): void;

    public function isChallengeAd(): bool;

    public function getAutoLapCount(): int;
}
