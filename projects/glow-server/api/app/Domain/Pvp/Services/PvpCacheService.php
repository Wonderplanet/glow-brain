<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Entities\PvpRankingItem;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\PvpMyRankingData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PvpCacheService
{
    private const TWO_WEEKS_SECONDS = 14 * 24 * 60 * 60; // 2週間の秒数
    private const MIN_USERS_FOR_RANKING = 1; // ランキング表示に必要な最小ユーザ数

    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function getOpponentCandidateRangeList(
        string $sysPvpSeasonId,
        string $rankClassType,
        int $rankClassLevel,
        int $minScore,
        int $maxScore
    ): array {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey($sysPvpSeasonId, $rankClassType, $rankClassLevel);
        $result = $this->cacheClientManager->getCacheClient()->zRevRangeByScore(
            $cacheKey,
            $maxScore,
            $minScore,
            false,
            PvpConstant::MATCHING_CACHE_GET_LIMIT
        );
        if ($result === false) {
            return [];
        }
        return $result;
    }

    public function deleteOpponentCandidate(
        string $sysPvpSeasonId,
        string $myId,
        string $rankClassType,
        int $rankClassLevel,
    ): void {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey($sysPvpSeasonId, $rankClassType, $rankClassLevel);
        $this->cacheClientManager->getCacheClient()->zRem(
            $cacheKey,
            [$myId]
        );
    }

    public function addOpponentCandidate(
        string $sysPvpSeasonId,
        string $myId,
        string $rankClassType,
        int $rankClassLevel,
        int $score
    ): void {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey($sysPvpSeasonId, $rankClassType, $rankClassLevel);
        $this->cacheClientManager->getCacheClient()->zAdd(
            $cacheKey,
            [$myId => $score]
        );
    }

    public function addOpponentStatus(
        string $sysPvpSeasonId,
        string $myId,
        OpponentPvpStatusData $opponentPvpStatusData,
    ): void {
        $cacheKey = CacheKeyUtil::getPvpOpponentStatusKey($sysPvpSeasonId, $myId);
        $this->cacheClientManager->getCacheClient()->set(
            $cacheKey,
            $opponentPvpStatusData,
            self::TWO_WEEKS_SECONDS
        );
    }

    public function getOpponentStatus(
        string $sysPvpSeasonId,
        string $myId,
    ): ?OpponentPvpStatusData {
        $cacheKey = CacheKeyUtil::getPvpOpponentStatusKey($sysPvpSeasonId, $myId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }

    /**
     * PVPランキングにスコアを追加
     * @param string $sysPvpSeasonId
     * @param string $usrUserId
     * @param int $score
     * @return void
     */
    public function addRankingScore(string $sysPvpSeasonId, string $usrUserId, int $score): void
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->zadd($cacheKey, [$usrUserId => $score]);
    }

    /**
     * PVPランキングのスコアをインクリメント
     * @param string $sysPvpSeasonId
     * @param string $usrUserId
     * @param int $deltaPoint
     * @return void
     */
    public function incrementRankingScore(string $sysPvpSeasonId, string $usrUserId, int $deltaPoint): void
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->zincrby($cacheKey, $deltaPoint, $usrUserId);
    }

    public function getRankingScore(string $sysPvpSeasonId, string $usrUserId): ?int
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $score = $this->cacheClientManager->getCacheClient()->zscore($cacheKey, $usrUserId);
        return $score !== false ? (int)$score : null;
    }

    public function getPvpRankingCacheTtl(string $sysPvpSeasonId): int
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        return $this->cacheClientManager->getCacheClient()->ttl($cacheKey);
    }

    /**
     * PVPのランキングデータをキャッシュから取得
     * @param string $sysPvpSeasonId
     * @return Collection<PvpRankingItem>|null
     */
    public function getPvpRankingCache(string $sysPvpSeasonId): Collection|null
    {
        $cacheKey = CacheKeyUtil::getPvpRankingCacheKey($sysPvpSeasonId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }

    /**
     * スコア０以上のユーザ数を取得
     * ０以下のスコアはランキングに含まれないため、０以下のユーザはカウントしない
     *
     * @param string $sysPvpSeasonId
     * @return integer
     */
    public function getRankingCount(string $sysPvpSeasonId): int
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        return $this->cacheClientManager->getCacheClient()->zCount($cacheKey, 0, '+inf');
    }

    public function isViewableRanking(string $sysPvpSeasonId): bool
    {
        $pvpRankingCache = $this->getPvpRankingCache($sysPvpSeasonId);
        if ($pvpRankingCache !== null) {
            return true; // キャッシュが存在する場合はランキング表示可能
        }

        $userCount = $this->getRankingCount($sysPvpSeasonId);
        return $userCount >= self::MIN_USERS_FOR_RANKING;
    }

    /**
     * PVPのランキングデータをキャッシュに設定
     * @param string     $sysPvpSeasonId
     * @param Collection<PvpRankingItem> $pvpRankingItems
     * @param int        $ttl
     * @return void
     */
    public function setPvpRankingCache(
        string $sysPvpSeasonId,
        Collection $pvpRankingItems,
        int $ttl
    ): void {
        $cacheKey = CacheKeyUtil::getPvpRankingCacheKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $pvpRankingItems, $ttl);
    }

    /**
     * ランキング上位{$topN}位のユーザーのusr_user_idとスコアのマップを取得
     * @param string $sysPvpSeasonId
     * @return array<string, float> usr_user_id => score
     */
    public function getTopRankedPlayerScoreMap(string $sysPvpSeasonId, int $topN): array
    {
        $cacheClient = $this->cacheClientManager->getCacheClient();
        // zcountでスコアが0以上(チーター以外)のユーザー数を取得
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $nonCheaterUserCount = $cacheClient->zCount($cacheKey, 0, '+inf');

        if ($nonCheaterUserCount <= $topN) {
            // チーターではない(スコアが0以上の)ユーザーが{$topN}件以下であれば全ユーザーが取得対象
            $minScore = 0;
        } else {
            // ランキングに{$topN}人以上存在するので上位から{$topN}番目のユーザーのスコア以上が取得対象(0始まりなので-1)
            $index = $topN - 1;
            $minScore = (int) current(
                $cacheClient->zRevRange($cacheKey, $index, $index, true)
            );
        }

        // 同率ユーザーが過剰となったときの大量データ取得を回避するために上限を設定する
        $limit = $topN + PvpConstant::RANKING_FETCH_BUFFER;
        return $cacheClient->zRevRangeByScore($cacheKey, '+inf', $minScore, true, $limit);
    }

    /**
     * 自身のランキング情報を集める
     * @param UsrPvpInterface|null $usrPvp
     * @return PvpMyRankingData
     */
    public function generatePvpMyRankingData(
        ?UsrPvpInterface $usrPvp,
    ): PvpMyRankingData {
        if ($this->isReplaceRankingWithTmp($usrPvp?->getSysPvpSeasonId() ?? '')) {
            // 期間限定で未参加として扱う
            $myScore = null;
            $myRank = null;
            $isExcludedRanking = false;
        } elseif (is_null($usrPvp) || ($usrPvp->getLastPlayedAt() === null)) {
            // ユーザーデータがないまたは最終プレイ日時がない場合はランキングに参加していない
            $myScore = null;
            $myRank = null;
            $isExcludedRanking = false;
        } else {
            $cacheClient = $this->cacheClientManager->getCacheClient();
            $cacheKey = CacheKeyUtil::getPvpRankingKey($usrPvp->getSysPvpSeasonId());
            $cacheScore = $cacheClient->zScore($cacheKey, $usrPvp->getUsrUserId());
            $myScore = $usrPvp->getScore();
            $isExcludedRanking = $usrPvp->isExcludedRanking();
            if ($cacheScore === false) {
                $myRank = null;
            } else {
                $myRank = $cacheClient->zCount($cacheKey, $myScore + 1, "+inf") + 1;
            }
        }
        return new PvpMyRankingData($myRank, $myScore, $isExcludedRanking);
    }

    public function getMyRanking(
        string $usrUserId,
        string $sysPvpSeasonId,
    ): ?int {
        if ($this->isReplaceRankingWithTmp($sysPvpSeasonId)) {
            return null; // 期間限定でランキング非表示
        }

        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $score = $this->cacheClientManager->getCacheClient()->zScore($cacheKey, $usrUserId);
        if ($score === false) {
            return null; // ランキングに存在しない
        }
        return $this->cacheClientManager->getCacheClient()->zCount($cacheKey, (int) $score + 1, "+inf") + 1;
    }

    public function isReplaceRankingWithTmp(string $sysPvpSeasonId): bool
    {
        return $sysPvpSeasonId === '2025039'
            && now() <= CarbonImmutable::parse('2025-10-01 5:00:00');
    }
}
