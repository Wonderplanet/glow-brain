<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrMissionInterface extends UsrModelInterface
{
    public function getMstMissionId(): string;
    public function getStatus(): int;
    public function getClearedAt(): ?string;
    public function getReceivedRewardAt(): ?string;
    public function getLatestUpdateAt(): string;
    public function clear(CarbonImmutable $now): void;
    public function receiveReward(CarbonImmutable $now): void;
    public function resetStatus(
        CarbonImmutable $now,
    ): void;
    public function isClear(): bool;
    public function isReceivedReward(): bool;
    public function canReceiveReward(): bool;
}
