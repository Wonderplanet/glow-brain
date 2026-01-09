<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Models;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use App\Http\Responses\Data\OpponentPvpStatusData;
use Carbon\CarbonImmutable;

class UsrPvp extends UsrEloquentModel implements UsrPvpInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'sys_pvp_season_id',
        'score',
        'max_received_score_reward',
        'pvp_rank_class_type',
        'pvp_rank_class_level',
        'ranking',
        'daily_remaining_challenge_count',
        'daily_remaining_item_challenge_count',
        'last_played_at',
        'selected_opponent_candidates',
        'is_excluded_ranking',
        'is_season_reward_received',
        'latest_reset_at',
    ];

    protected $casts = [
        'is_excluded_ranking' => 'bool',
        'is_season_reward_received' => 'bool',
        'selected_opponent_candidates' => 'json',
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->sys_pvp_season_id;
    }

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getSysPvpSeasonId(): string
    {
        return $this->sys_pvp_season_id;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getMaxReceivedScoreReward(): int
    {
        return $this->max_received_score_reward;
    }

    public function receiveScoreReward(): void
    {
        // 受け取り済みの最大スコアを更新する
        $this->max_received_score_reward = $this->score;
    }

    public function getPvpRankClassType(): string
    {
        return $this->pvp_rank_class_type;
    }

    public function getPvpRankClassTypeEnum(): PvpRankClassType
    {
        return $this->pvp_rank_class_type
            ? PvpRankClassType::tryFrom($this->pvp_rank_class_type)
            : PvpRankClassType::BRONZE;
    }

    public function getPvpRankClassLevel(): int
    {
        return $this->pvp_rank_class_level;
    }

    public function updatePvpRankClass(PvpRankClassType $pvpRankClassType, int $pvpRankClassLevel): void
    {
        $this->pvp_rank_class_type = $pvpRankClassType->value;
        $this->pvp_rank_class_level = $pvpRankClassLevel;
    }

    public function getRanking(): ?int
    {
        return $this->ranking;
    }

    public function updateRanking(int $ranking): void
    {
        $this->ranking = $ranking;
    }

    public function getDailyRemainingChallengeCount(): int
    {
        return $this->daily_remaining_challenge_count;
    }

    public function tryDecrementDailyRemainingChallengeCount(): bool
    {
        if ($this->daily_remaining_challenge_count <= 0) {
            return false;
        }
        $this->daily_remaining_challenge_count--;
        return true;
    }

    public function getDailyRemainingItemChallengeCount(): int
    {
        return $this->daily_remaining_item_challenge_count;
    }

    public function tryDecrementDailyRemainingItemChallengeCount(): bool
    {
        if ($this->daily_remaining_item_challenge_count <= 0) {
            return false;
        }
        $this->daily_remaining_item_challenge_count--;
        return true;
    }

    public function isExcludedRanking(): bool
    {
        return $this->is_excluded_ranking;
    }

    public function setIsExcludedRanking(bool $isExcludedRanking): void
    {
        $this->is_excluded_ranking = $isExcludedRanking;
    }

    public function isSeasonRewardReceived(): bool
    {
        return $this->is_season_reward_received;
    }

    public function setIsSeasonRewardReceived(bool $isSeasonRewardReceived): void
    {
        $this->is_season_reward_received = $isSeasonRewardReceived;
    }

    public function getLastPlayedAt(): ?string
    {
        return $this->last_played_at;
    }

    public function setLastPlayedAt(CarbonImmutable $now): void
    {
        $this->last_played_at = $now->toDateTimeString();
    }

    public function isPlayed(): bool
    {
        return !is_null($this->last_played_at);
    }

    public function getSelectedOpponentCandidates(): string
    {
        return json_encode($this->selected_opponent_candidates, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<mixed>
     */
    public function getSelectedOpponentCandidatesToArray(): array
    {
        return $this->selected_opponent_candidates ?? [];
    }

    /**
     * 選択された対戦相手候補を設定する
     * @param \Illuminate\Support\Collection<OpponentPvpStatusData> $opponentDatas
     */
    public function setSelectedOpponentCandidates(\Illuminate\Support\Collection $opponentDatas): void
    {
        $data = $opponentDatas->mapWithKeys(function (OpponentPvpStatusData $opponent) {
            $myId = $opponent->getPvpUserProfile()->getMyId();
            return [$myId => json_decode($opponent->formatToJson(), true)];
        })->toArray();

        $this->selected_opponent_candidates = $data;
    }

    public function adjustScore(int $score): void
    {
        $this->score += $score;
        $this->score = max(0, $this->score);
    }

    public function setScore(int $score): void
    {
        $this->score = max(0, $score);
    }

    /**
     * 1日のアイテム消費なし挑戦可能回数とアイテム消費あり挑戦可能回数を初期化するメソッド
     */
    public function resetRemainingChallengeCounts(
        int $remainingChallengeCount,
        int $remainingItemChallengeCount,
        CarbonImmutable $now
    ): void {
        $this->daily_remaining_challenge_count = $remainingChallengeCount;
        $this->daily_remaining_item_challenge_count = $remainingItemChallengeCount;
        $this->latest_reset_at = $now->toDateTimeString();
    }

    public function getLatestResetAt(): string
    {
        return $this->latest_reset_at;
    }
}
