<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $usr_user_id
 * @property string $mst_stage_id
 * @property int $clear_count
 * @property int $reset_clear_count
 * @property int $reset_ad_challenge_count
 * @property string|null $latest_reset_at
 * @property string|null $last_challenged_at
 * @property string $latest_event_setting_end_at
 */
class UsrStageEvent extends UsrEloquentModel implements UsrStageEventInterface
{
    use HasFactory;

    private bool $isFirstClear = false;

    protected $guarded = [
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_stage_id;
    }

    public function getMstStageId(): string
    {
        return $this->mst_stage_id;
    }

    public function isClear(): bool
    {
        return $this->clear_count > 0;
    }

    public function isResetClear(): bool
    {
        return $this->reset_clear_count > 0;
    }

    public function getClearCount(): int
    {
        return $this->clear_count;
    }

    public function getResetClearCount(): int
    {
        return $this->reset_clear_count;
    }

    public function incrementClearCount(): void
    {
        if ($this->reset_clear_count === 0) {
            $this->isFirstClear = true;
        }
        $this->clear_count++;
        $this->reset_clear_count++;
    }

    public function addClearCount(int $addNum): void
    {
        if ($this->reset_clear_count === 0) {
            $this->isFirstClear = true;
        }
        $this->clear_count += $addNum;
        $this->reset_clear_count += $addNum;
    }

    public function isFirstClear(): bool
    {
        return $this->isFirstClear;
    }

    public function getResetAdChallengeCount(): int
    {
        return $this->reset_ad_challenge_count;
    }

    public function getResetClearTimeMs(): ?int
    {
        return $this->reset_clear_time_ms;
    }

    public function getClearTimeMs(): ?int
    {
        return $this->clear_time_ms;
    }

    public function setClearTimeMsAndResetClearTimeMs(int $clearTimeMs): void
    {
        if (is_null($this->clear_time_ms)) {
            $this->clear_time_ms = $clearTimeMs;
        } else {
            $this->clear_time_ms = min($this->clear_time_ms, $clearTimeMs);
        }

        if (is_null($this->reset_clear_time_ms)) {
            $this->reset_clear_time_ms = $clearTimeMs;
        } else {
            $this->reset_clear_time_ms = min($this->reset_clear_time_ms, $clearTimeMs);
        }
    }

    public function incrementResetAdChallengeCount(): void
    {
        $this->reset_ad_challenge_count++;
    }

    public function getLatestResetAt(): ?string
    {
        return $this->latest_reset_at;
    }

    public function getLatestEventSettingEndAt(): string
    {
        return $this->latest_event_setting_end_at;
    }

    public function resetResetClearTimeMs(string $eventSettingEndAt): void
    {
        $this->reset_clear_time_ms = null;
        $this->latest_event_setting_end_at = $eventSettingEndAt;
    }

    private function setLatestResetAt(string $latestResetAt): void
    {
        $this->latest_reset_at = $latestResetAt;
    }

    private function resetClearCount(): void
    {
        $this->reset_clear_count = 0;
    }

    private function resetAdChallengeCount(): void
    {
        $this->reset_ad_challenge_count = 0;
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->resetClearCount();
        $this->resetAdChallengeCount();
        $this->setLatestResetAt($now->toDateTimeString());
    }

    public function getLastChallengedAt(): ?string
    {
        return $this->last_challenged_at;
    }

    public function setLastChallengedAt(string $lastChallengedAt): void
    {
        $this->last_challenged_at = $lastChallengedAt;
    }
}
