<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;

class AdventBattleCacheService
{

    public function __construct(
        private CacheClientManager $cacheClientManager
    ) {
    }

    /**
     * 降臨バトルのランキングにスコアを追加
     * @param string $mstAdventBattleId
     * @param array  $data
     * @return void
     */
    public function addRankingScoreAll(string $mstAdventBattleId, array $data): void
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $this->cacheClientManager->getCacheClient()->zadd($cacheKey, $data);
    }

    public function removeRanking(string $mstAdventBattleId, string $usrUserId): void
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $this->cacheClientManager->getCacheClient()->zRem($cacheKey, [$usrUserId]);
    }

    /**
     * ユーザーのランキングスコアを取得
     * @param string $mstAdventBattleId
     * @param string $usrUserId
     * @return int|null
     */
    public function getRankingScore(string $mstAdventBattleId, string $usrUserId): ?int
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $score = $this->cacheClientManager->getCacheClient()->zscore($cacheKey, $usrUserId);
        return $score !== false ? (int)$score : null;
    }

    /**
     * ユーザーのランキング順位を取得
     * @param string $usrUserId
     * @param string $mstAdventBattleId
     * @return int|null
     */
    public function getMyRanking(
        string $usrUserId,
        string $mstAdventBattleId,
    ): ?int {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $score = $this->cacheClientManager->getCacheClient()->zScore($cacheKey, $usrUserId);
        if ($score === false) {
            return null; // ランキングに存在しない
        }
        return $this->cacheClientManager->getCacheClient()->zCount($cacheKey, (int) $score + 1, "+inf") + 1;
    }

    /**
     * 降臨バトルランキングデータキャッシュのTTLを取得
     * @param string $mstAdventBattleId
     * @return int
     */
    public function getAdventBattleRankingCacheTtl(string $mstAdventBattleId): int
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        return $this->cacheClientManager->getCacheClient()->ttl($cacheKey);
    }

    /**
     * 協力バトル全ユーザー累計スコアを取得
     * @param string $mstAdventBattleId
     * @return int
     */
    public function getRaidTotalScore(string $mstAdventBattleId): int
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        $value = $this->cacheClientManager->getCacheClient()->get($cacheKey);
        return $value === null ? 0 : (int) $value;
    }

    /**
     * 協力バトル全ユーザー累計スコアを設定
     * @param string $mstAdventBattleId
     * @param int $score
     * @return void
     */
    public function setRaidTotalScore(string $mstAdventBattleId, int $score): void
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        // set()はserialize()して保存するため、incrBy()が失敗する。
        // del()してからincrBy()で生の整数値として保存することで互換性を保つ。
        $this->cacheClientManager->getCacheClient()->del($cacheKey);
        $this->cacheClientManager->getCacheClient()->incrBy($cacheKey, $score);
    }
}
