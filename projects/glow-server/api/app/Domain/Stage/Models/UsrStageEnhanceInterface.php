<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use Carbon\CarbonImmutable;

interface UsrStageEnhanceInterface extends IBaseUsrStage
{
    public function getMstStageId(): string;
    public function getClearCount(): int;
    public function getResetChallengeCount(): int;
    public function getResetAdChallengeCount(): int;
    public function getMaxScore(): int;
    public function setMaxScore(int $score): void;
    public function getLatestResetAt(): ?string;
    public function addResetChallengeCount(int $addNum): void;
    public function incrementChallengeCount(bool $isChallengeAd): void;
    public function decrementChallengeCount(bool $isChallengeAd): void;
    public function reset(CarbonImmutable $now): void;
}
