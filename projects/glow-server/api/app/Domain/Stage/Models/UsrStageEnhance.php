<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

class UsrStageEnhance extends UsrEloquentModel implements UsrStageEnhanceInterface
{
    use HasFactory;

    private bool $isFirstClear = false;

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_stage_id;
    }

    public function getMstStageId(): string
    {
        return $this->mst_stage_id;
    }

    public function getClearCount(): int
    {
        return $this->clear_count;
    }

    public function getResetChallengeCount(): int
    {
        return $this->reset_challenge_count;
    }

    public function getResetAdChallengeCount(): int
    {
        return $this->reset_ad_challenge_count;
    }

    public function getMaxScore(): int
    {
        return $this->max_score;
    }

    /**
     * 過去通算最大スコアを更新
     * 既存のスコアよりも大きい場合のみ更新する
     *
     * @param int $score
     * @return void
     */
    public function setMaxScore(int $score): void
    {
        if ($this->max_score >= $score) {
            return;
        }
        $this->max_score = $score;
    }

    public function getLatestResetAt(): ?string
    {
        return $this->latest_reset_at;
    }

    public function incrementClearCount(): void
    {
        if ($this->clear_count === 0) {
            $this->isFirstClear = true;
        }
        $this->clear_count++;
    }

    public function addClearCount(int $addNum): void
    {
        if ($this->clear_count === 0) {
            $this->isFirstClear = true;
        }
        $this->clear_count += $addNum;
    }

    public function isClear(): bool
    {
        return $this->clear_count > 0;
    }

    public function isFirstClear(): bool
    {
        return $this->isFirstClear;
    }

    public function addResetChallengeCount(int $addNum): void
    {
        $this->reset_challenge_count += $addNum;
    }

    public function incrementChallengeCount(bool $isChallengeAd): void
    {
        if ($isChallengeAd) {
            $this->reset_ad_challenge_count++;
        } else {
            $this->reset_challenge_count++;
        }
    }

    public function decrementChallengeCount(bool $isChallengeAd): void
    {
        if ($isChallengeAd) {
            $this->reset_ad_challenge_count = max(0, $this->reset_ad_challenge_count - 1);
        } else {
            $this->reset_challenge_count = max(0, $this->reset_challenge_count - 1);
        }
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->reset_challenge_count = 0;
        $this->reset_ad_challenge_count = 0;
        $this->latest_reset_at = $now->toDateTimeString();
    }
}
