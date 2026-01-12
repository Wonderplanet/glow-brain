<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface IUsrMission extends UsrModelInterface
{
    public function getMissionType(): int;

    public function getMstMissionId(): string;

    public function getStatus(): int;

    public function getIsOpen(): int;

    public function getProgress(): int;

    public function getUnlockProgress(): int;

    public function getLatestResetAt(): string;

    public function getClearedAt(): ?string;

    public function getReceivedRewardAt(): ?string;

    public function reset(CarbonImmutable $now): void;

    public function updateProgress(int $progress): void;

    public function updateUnlockProgress(int $unlockProgress): void;

    public function clear(CarbonImmutable $now): void;

    public function open(): void;

    public function receiveReward(CarbonImmutable $now): void;

    public function isClear(): bool;

    public function isOpen(): bool;

    public function canReceiveReward(): bool;

    public function isReceivedReward(): bool;
}
