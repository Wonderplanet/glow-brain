<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Http\Responses\Data\AdventBattleMyRankingData;
use Illuminate\Support\Collection;

readonly class AdventBattleCacheService
{
    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * 協力バトル累計ダメージキャッシュから取得
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
     * 協力バトル累計ダメージキャッシュをインクリメントして取得
     * @param string $mstAdventBattleId
     * @param int $score
     * @return int
     */
    public function incrementRaidTotalScore(string $mstAdventBattleId, int $score): int
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        return $this->cacheClientManager->getCacheClient()->incrBy($cacheKey, $score);
    }

    /**
     * 降臨バトルランキングデータキャッシュのTTLを取得
     * @param string $mstAdventBattleId
     * @return int
     */
    public function getAdventBattleRankingCacheTtl(string $mstAdventBattleId): int
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingCacheKey($mstAdventBattleId);
        return $this->cacheClientManager->getCacheClient()->ttl($cacheKey);
    }

    /**
     * 降臨バトルのランキングデータをキャッシュを取得
     * @param string $mstAdventBattleId
     * @return Collection|null
     */
    public function getAdventBattleRankingCache(string $mstAdventBattleId): ?Collection
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingCacheKey($mstAdventBattleId);
        $cacheClient = $this->cacheClientManager->getCacheClient();
        try {
            $result = $cacheClient->get($cacheKey);
            // キャッシュのデータに問題ないか１つだけ確認してから返す
            collect($result)->first()?->formatToResponse(); // @phpstan-ignore-line
            return $result;
        } catch (\Throwable $e) {
            // エラーする場合はランキングデータに破損があるためキャッシュを削除してnullを返す
            $cacheClient->del($cacheKey);
            logger()->warning("AdventBattleRankingCache corrupted. mstAdventBattleId: $mstAdventBattleId");
            return null;
        }
    }

    /**
     * 降臨バトルのランキングデータをキャッシュに設定
     * @param string     $mstAdventBattleId
     * @param Collection $adventBattleRankingItems
     * @param int        $ttl
     * @return void
     */
    public function setAdventBattleRankingCache(
        string $mstAdventBattleId,
        Collection $adventBattleRankingItems,
        int $ttl
    ): void {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingCacheKey($mstAdventBattleId);
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $adventBattleRankingItems, $ttl);
    }

    /**
     * 降臨バトルのランキングにスコアを追加
     * @param string $mstAdventBattleId
     * @param string $usrUserId
     * @param int    $score
     * @return void
     */
    public function addRankingScore(string $mstAdventBattleId, string $usrUserId, int $score): void
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $this->cacheClientManager->getCacheClient()->zadd($cacheKey, [$usrUserId => $score]);
    }

    /**
     * 降臨バトルのランキングのスコアをインクリメント
     * @param string $mstAdventBattleId
     * @param string $usrUserId
     * @param int    $score
     * @return void
     */
    public function incrementRankingScore(string $mstAdventBattleId, string $usrUserId, int $score): void
    {
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $this->cacheClientManager->getCacheClient()->zincrby($cacheKey, $score, $usrUserId);
    }

    /**
     * ランキング上位200位のユーザーのusr_user_idとスコアのマップを取得
     * @param string $mstAdventBattleId
     * @return array<string, float> usr_user_id => score
     */
    public function getTopRankedPlayerScoreMap(string $mstAdventBattleId): array
    {
        $cacheClient = $this->cacheClientManager->getCacheClient();
        // zcountでスコアが0以上(チーター以外)のユーザー数を取得
        $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $nonCheaterUserCount = $cacheClient->zCount($cacheKey, 0, '+inf');

        if ($nonCheaterUserCount <= AdventBattleConstant::RANKING_DISPLAY_LIMIT) {
            // チーターではない(スコアが0以上の)ユーザーが200件以下であれば全ユーザーが取得対象
            $minScore = 0;
        } else {
            // ランキングに200人以上存在するので上位から200番目のユーザーのスコア以上が取得対象(0始まりなので-1)
            $index = AdventBattleConstant::RANKING_DISPLAY_LIMIT - 1;
            $minScore = (int) current(
                $cacheClient->zRevRange($cacheKey, $index, $index, true)
            );
        }

        // 同率ユーザーが過剰となったときの大量データ取得を回避するために上限を設定する
        $limit = AdventBattleConstant::RANKING_DISPLAY_LIMIT + AdventBattleConstant::RANKING_FETCH_BUFFER;
        return $cacheClient->zRevRangeByScore($cacheKey, '+inf', $minScore, true, $limit);
    }

    /**
     * 自身のランキング情報を集める
     * @param UsrAdventBattleInterface|null $usrAdventBattle
     * @return AdventBattleMyRankingData
     */
    public function generateAdventBattleMyRankingData(
        ?UsrAdventBattleInterface $usrAdventBattle,
    ): AdventBattleMyRankingData {
        // 自分のランキングデータを作る

        if (is_null($usrAdventBattle) || $usrAdventBattle->getMaxScore() === 0) {
            // ユーザーデータがないまたはスコアが0 = 降臨バトル未参加
            $myScore = null;
            $myRank = null;
            $totalScore = null;
            $isExcludedRanking = false;
        } else {
            // 自身よりもスコアが高いユーザー数+1が自身の順位
            $cacheClient = $this->cacheClientManager->getCacheClient();
            $cacheKey = CacheKeyUtil::getAdventBattleRankingKey($usrAdventBattle->getMstAdventBattleId());
            $myScore = $usrAdventBattle->getMaxScore();
            $myRank = $cacheClient->zCount($cacheKey, $myScore + 1, "+inf") + 1;
            $totalScore = $usrAdventBattle->getTotalScore();
            $isExcludedRanking = $usrAdventBattle->isExcludedRanking();
        }
        return new AdventBattleMyRankingData(
            $myRank,
            $myScore,
            $totalScore,
            $isExcludedRanking
        );
    }
}
