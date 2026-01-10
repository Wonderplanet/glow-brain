<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Mission\Constants\MissionConstant;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionEventInterface;
use App\Domain\Resource\Usr\Models\UsrModel;
use Carbon\CarbonImmutable;

class UsrMissionEvent extends UsrModel implements UsrMissionEventInterface, IUsrMission
{
    protected static string $tableName = 'usr_mission_events';
    protected array $modelKeyColumns = ['usr_user_id', 'mission_type', 'mst_mission_id'];

    public static function create(
        string $usrUserId,
        int $missionType,
        string $mstMissionId,
        CarbonImmutable $now,
    ): UsrMissionEventInterface {
        return new self([
            'usr_user_id' => $usrUserId,
            'mission_type' => $missionType,
            'mst_mission_id' => $mstMissionId,
            'status' => MissionStatus::UNCLEAR->value,
            'is_open' => MissionUnlockStatus::LOCK->value,
            'progress' => MissionConstant::PROGRESS_INITIAL_VALUE,
            'unlock_progress' => MissionConstant::PROGRESS_INITIAL_VALUE,
            'latest_reset_at' => $now->toDateTimeString(),
            'cleared_at' => null,
            'received_reward_at' => null,
        ]);
    }

    public function getMissionType(): int
    {
        return $this->attributes['mission_type'];
    }

    public function getMstMissionId(): string
    {
        return $this->attributes['mst_mission_id'];
    }

    public function getStatus(): int
    {
        return $this->attributes['status'];
    }

    public function getIsOpen(): int
    {
        return $this->attributes['is_open'];
    }

    public function getProgress(): int
    {
        return $this->attributes['progress'];
    }

    public function getUnlockProgress(): int
    {
        return $this->attributes['unlock_progress'];
    }

    public function getLatestResetAt(): string
    {
        return $this->attributes['latest_reset_at'];
    }

    public function getClearedAt(): ?string
    {
        return $this->attributes['cleared_at'];
    }

    public function getReceivedRewardAt(): ?string
    {
        return $this->attributes['received_reward_at'];
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->attributes['status'] = MissionStatus::UNCLEAR->value;
        $this->attributes['is_open'] = MissionUnlockStatus::LOCK->value;
        $this->attributes['progress'] = MissionConstant::PROGRESS_INITIAL_VALUE;
        $this->attributes['unlock_progress'] = MissionConstant::PROGRESS_INITIAL_VALUE;
        $this->attributes['latest_reset_at'] = $now->toDateTimeString();
        $this->attributes['cleared_at'] = null;
        $this->attributes['received_reward_at'] = null;
    }

    public function open(): void
    {
        $this->attributes['is_open'] = MissionUnlockStatus::OPEN->value;
    }

    public function updateProgress(int $progress): void
    {
        $this->attributes['progress'] = $progress;
    }

    public function updateUnlockProgress(int $unlockProgress): void
    {
        $this->attributes['unlock_progress'] = $unlockProgress;
    }

    public function clear(CarbonImmutable $now): void
    {
        $this->attributes['status'] = MissionStatus::CLEAR->value;
        $this->attributes['cleared_at'] = $now->toDateTimeString();
    }

    public function receiveReward(CarbonImmutable $now): void
    {
        $this->attributes['status'] = MissionStatus::RECEIVED_REWARD->value;
        $this->attributes['received_reward_at'] = $now->toDateTimeString();
    }

    public function isClear(): bool
    {
        return $this->attributes['status'] >= MissionStatus::CLEAR->value;
    }

    public function isReceivedReward(): bool
    {
        return $this->attributes['status'] === MissionStatus::RECEIVED_REWARD->value;
    }

    public function canReceiveReward(): bool
    {
        return $this->attributes['status'] === MissionStatus::CLEAR->value
            && $this->attributes['is_open'] === MissionUnlockStatus::OPEN->value;
    }

    public function isOpen(): bool
    {
        return $this->attributes['is_open'] === MissionUnlockStatus::OPEN->value;
    }
}
