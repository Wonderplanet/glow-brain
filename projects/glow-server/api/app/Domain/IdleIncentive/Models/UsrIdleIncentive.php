<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

class UsrIdleIncentive extends UsrEloquentModel implements UsrIdleIncentiveInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
    ];

    protected $casts = [
    ];

    public function getDiamondQuickReceiveCount(): int
    {
        return $this->diamond_quick_receive_count;
    }

    public function setDiamondQuickReceiveCount(int $diamondQuickReceiveCount): void
    {
        $this->diamond_quick_receive_count = $diamondQuickReceiveCount;
    }

    public function getAdQuickReceiveCount(): int
    {
        return $this->ad_quick_receive_count;
    }

    public function setAdQuickReceiveCount(int $adQuickReceiveCount): void
    {
        $this->ad_quick_receive_count = $adQuickReceiveCount;
    }

    public function getIdleStartedAt(): string
    {
        return $this->idle_started_at;
    }

    public function setIdleStartedAt(string $idleStartedAt): void
    {
        $this->idle_started_at = $idleStartedAt;
    }

    public function incrementDiamondQuickReceiveCount(): void
    {
        $this->diamond_quick_receive_count++;
    }

    public function incrementAdQuickReceiveCount(): void
    {
        $this->ad_quick_receive_count++;
    }

    public function getDiamondQuickReceiveAt(): string
    {
        return $this->diamond_quick_receive_at;
    }

    public function setDiamondQuickReceiveAt(string $diamondQuickReceiveAt): void
    {
        $this->diamond_quick_receive_at = $diamondQuickReceiveAt;
    }

    public function getAdQuickReceiveAt(): string
    {
        return $this->ad_quick_receive_at;
    }

    public function setAdQuickReceiveAt(string $adQuickReceiveAt): void
    {
        $this->ad_quick_receive_at = $adQuickReceiveAt;
    }

    public function getRewardMstStageId(): ?string
    {
        return $this->reward_mst_stage_id;
    }

    public function setRewardMstStageId(?string $rewardMstStageId): void
    {
        $this->reward_mst_stage_id = $rewardMstStageId;
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }
}
