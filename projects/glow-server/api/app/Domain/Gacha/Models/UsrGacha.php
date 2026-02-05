<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

class UsrGacha extends UsrEloquentModel implements UsrGachaInterface
{
    use HasFactory;

    protected $fillable = [
    ];

    protected $casts = [
    ];

    public function init(string $usrUserId, string $oprGachaId): void
    {
        $this->id = $this->newUniqueId();
        $this->usr_user_id = $usrUserId;
        $this->opr_gacha_id = $oprGachaId;
        $this->ad_played_at = null;
        $this->played_at = null;
        $this->ad_count = 0;
        $this->ad_daily_count = 0;
        $this->count = 0;
        $this->daily_count = 0;
        $this->expires_at = null;
        $this->current_step_number = null;
        $this->loop_count = null;
    }

    public function getOprGachaId(): string
    {
        return $this->opr_gacha_id;
    }

    public function getAdPlayedAt(): ?string
    {
        return $this->ad_played_at;
    }

    public function getPlayedAt(): ?string
    {
        return $this->played_at;
    }

    public function getAdCount(): int
    {
        return $this->ad_count;
    }

    public function getAdDailyCount(): int
    {
        return $this->ad_daily_count;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getDailyCount(): int
    {
        return $this->daily_count;
    }

    public function resetAdDailyCount(): void
    {
        $this->ad_daily_count = 0;
    }

    public function resetDailyCount(): void
    {
        $this->daily_count = 0;
    }

    public function incrementAdPlayCount(int $addNum): void
    {
        $this->ad_daily_count += $addNum;
        $this->ad_count += $addNum;
    }

    public function incrementPlayCount(int $addNum): void
    {
        $this->daily_count += $addNum;
        $this->count += $addNum;
    }

    public function setAdPlayedAt(string $playAt): void
    {
        $this->ad_played_at = $playAt;
    }

    public function setPlayedAt(string $playAt): void
    {
        $this->played_at = $playAt;
    }

    public function setExpiresAt(string $expiresAt): void
    {
        $this->expires_at = $expiresAt;
    }

    public function getExpiresAt(): ?string
    {
        return $this->expires_at;
    }

    public function getCurrentStepNumber(): ?int
    {
        return $this->current_step_number;
    }

    public function setCurrentStepNumber(?int $currentStepNumber): void
    {
        $this->current_step_number = $currentStepNumber;
    }

    public function getLoopCount(): ?int
    {
        return $this->loop_count;
    }

    public function setLoopCount(?int $loopCount): void
    {
        $this->loop_count = $loopCount;
    }
}
