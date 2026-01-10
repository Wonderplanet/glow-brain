<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class UsrMissionStatusData
{
    public function __construct(
        private string $mstMissionId,
        private int $progress,
        private bool $isCleared,
        private bool $isReceivedReward,
        private ?string $groupId = null,
    ) {
    }

    public function getMstMissionId(): string
    {
        return $this->mstMissionId;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getIsCleared(): bool
    {
        return $this->isCleared;
    }

    public function getIsReceivedReward(): bool
    {
        return $this->isReceivedReward;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }
}
