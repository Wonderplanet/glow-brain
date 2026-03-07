<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $usr_user_id
 * @property string $mst_advent_battle_id
 * @property int $max_score
 * @property int $total_score
 * @property int $challenge_count
 * @property int $reset_challenge_count
 * @property int $reset_ad_challenge_count
 * @property int $clear_count
 * @property int $max_received_max_score_reward
 * @property string|null $received_rank_reward_group_id
 * @property string|null $received_raid_reward_group_id
 * @property bool $is_ranking_reward_received
 * @property bool $is_excluded_ranking
 * @property string|null $latest_reset_at
 * @property string|null $max_score_party
 */
class UsrAdventBattle extends UsrEloquentModel implements UsrAdventBattleInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mst_advent_battle_id',
        'max_score',
        'total_score',
        'challenge_count',
        'reset_challenge_count',
        'reset_ad_challenge_count',
        'clear_count',
        'max_received_max_score_reward',
        'received_rank_reward_group_id',
        'received_raid_reward_group_id',
        'is_ranking_reward_received',
        'is_excluded_ranking',
        'latest_reset_at',
        'max_score_party',
    ];

    protected $casts = [
        'is_ranking_reward_received' => 'bool',
        'is_excluded_ranking' => 'bool',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_advent_battle_id;
    }

    public function getMstAdventBattleId(): string
    {
        return $this->mst_advent_battle_id;
    }

    public function getMaxScore(): int
    {
        return $this->max_score;
    }

    public function setMaxScore(int $score): void
    {
        $this->max_score = $score;
    }

    public function getTotalScore(): int
    {
        return $this->total_score;
    }

    public function setTotalScore(int $score): void
    {
        $this->total_score = $score;
    }

    public function getChallengeCount(): int
    {
        return $this->challenge_count;
    }

    public function setChallengeCount(int $count): void
    {
        $this->challenge_count = $count;
    }

    public function getResetChallengeCount(): int
    {
        return $this->reset_challenge_count;
    }

    public function setResetChallengeCount(int $count): void
    {
        $this->reset_challenge_count = $count;
    }

    public function getResetAdChallengeCount(): int
    {
        return $this->reset_ad_challenge_count;
    }

    public function setResetAdChallengeCount(int $count): void
    {
        $this->reset_ad_challenge_count = $count;
    }

    public function getClearCount(): int
    {
        return $this->clear_count;
    }

    public function setClearCount(int $count): void
    {
        $this->clear_count = $count;
    }

    public function getMaxReceivedMaxScoreReward(): int
    {
        return $this->max_received_max_score_reward;
    }

    public function setMaxReceivedMaxScoreReward(int $maxScore): void
    {
        $this->max_received_max_score_reward = $maxScore;
    }

    public function receiveMaxScoreReward(): void
    {
        // 受け取り済みの最大スコアを更新する
        $this->max_received_max_score_reward = $this->max_score;
    }

    public function getReceivedRankRewardGroupId(): ?string
    {
        return $this->received_rank_reward_group_id;
    }

    public function setReceivedRankRewardGroupId(string $groupId): void
    {
        $this->received_rank_reward_group_id = $groupId;
    }

    public function getReceivedRaidRewardGroupId(): ?string
    {
        return $this->received_raid_reward_group_id;
    }

    public function setReceivedRaidRewardGroupId(string $groupId): void
    {
        $this->received_raid_reward_group_id = $groupId;
    }

    public function setIsRankingRewardReceived(bool $isReceived): void
    {
        $this->is_ranking_reward_received = $isReceived;
    }

    public function isRankingRewardReceived(): bool
    {
        return $this->is_ranking_reward_received;
    }

    public function isExcludedRanking(): bool
    {
        return $this->is_excluded_ranking;
    }

    public function setIsExcludedRanking(bool $isExcludedRanking): void
    {
        $this->is_excluded_ranking = $isExcludedRanking;
    }

    public function getLatestResetAt(): ?string
    {
        return $this->latest_reset_at;
    }

    public function getMaxScoreParty(): ?string
    {
        return $this->max_score_party;
    }

    public function getMaxScorePartyArray(): array
    {
        return json_decode($this->max_score_party ?? '[]', true) ?? [];
    }

    public function setMaxScoreParty(array $party): void
    {
        $this->max_score_party = json_encode($party);
    }

    public function incrementChallengeCount(bool $isChallengeAd): void
    {
        $this->challenge_count++;
        if ($isChallengeAd) {
            $this->reset_ad_challenge_count++;
        } else {
            $this->reset_challenge_count++;
        }
    }

    public function decrementChallengeCount(bool $isChallengeAd): void
    {
        if ($this->challenge_count > 0) {
            $this->challenge_count--;
        }
        if ($isChallengeAd) {
            if ($this->reset_ad_challenge_count > 0) {
                $this->reset_ad_challenge_count--;
            }
        } else {
            if ($this->reset_challenge_count > 0) {
                $this->reset_challenge_count--;
            }
        }
    }

    public function incrementClearCount(): void
    {
        $this->clear_count++;
    }

    public function isFirstClear(): bool
    {
        return $this->clear_count === 1;
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->reset_challenge_count = 0;
        $this->reset_ad_challenge_count = 0;
        $this->latest_reset_at = $now->toDateTimeString();
    }
}
