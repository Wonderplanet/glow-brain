<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrIdleIncentiveInterface extends UsrModelInterface
{
    public function getDiamondQuickReceiveCount(): int;

    public function setDiamondQuickReceiveCount(int $diamondQuickReceiveCount): void;

    public function getAdQuickReceiveCount(): int;

    public function setAdQuickReceiveCount(int $adQuickReceiveCount): void;

    public function getIdleStartedAt(): string;

    public function setIdleStartedAt(string $idleStartedAt): void;

    public function incrementDiamondQuickReceiveCount(): void;

    public function incrementAdQuickReceiveCount(): void;

    public function getDiamondQuickReceiveAt(): string;

    public function setDiamondQuickReceiveAt(string $diamondQuickReceiveAt): void;

    public function getAdQuickReceiveAt(): string;

    public function setAdQuickReceiveAt(string $adQuickReceiveAt): void;

    public function getRewardMstStageId(): ?string;

    public function setRewardMstStageId(?string $rewardMstStageId): void;
}
