<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use Carbon\CarbonImmutable;

interface UsrStageEventInterface extends IBaseUsrStage
{
    public function getResetClearCount(): int;

    public function getResetAdChallengeCount(): int;

    public function getResetClearTimeMs(): ?int;

    public function getClearTimeMs(): ?int;

    public function setClearTimeMsAndResetClearTimeMs(int $clearTimeMs): void;

    public function incrementResetAdChallengeCount(): void;

    public function getLatestResetAt(): ?string;

    public function getLatestEventSettingEndAt(): string;

    public function resetResetClearTimeMs(string $eventSettingEndAt): void;

    public function reset(CarbonImmutable $now): void;

    public function getLastChallengedAt(): ?string;

    public function setLastChallengedAt(string $lastChallengedAt): void;
}
