<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrGachaInterface extends UsrModelInterface
{
    public function getOprGachaId(): string;

    public function getAdPlayedAt(): ?string;

    public function getPlayedAt(): ?string;

    public function getAdCount(): int;

    public function getAdDailyCount(): int;

    public function getCount(): int;

    public function getDailyCount(): int;

    public function resetAdDailyCount(): void;

    public function resetDailyCount(): void;

    public function incrementAdPlayCount(int $addNum): void;

    public function incrementPlayCount(int $addNum): void;

    public function setAdPlayedAt(string $adPlayAt): void;

    public function setPlayedAt(string $playAt): void;

    public function setExpiresAt(string $expiresAt): void;

    public function getExpiresAt(): ?string;

    public function getCurrentStepNumber(): ?int;

    public function setCurrentStepNumber(?int $currentStepNumber): void;

    public function getLoopCount(): ?int;

    public function setLoopCount(?int $loopCount): void;
}
