<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

class CacheKeyUtil
{
    /**
     * ガシャ提供割合のキー
     * @param string $oprGachaId
     * @return string
     */
    public static function getGachaProbabilityKey(string $oprGachaId): string
    {
        return "gacha:{$oprGachaId}:probability";
    }

    /**
     * 協力バトル累計ダメージキャッシュのキー
     * @param string $mstAdventBattleId
     * @return string
     */
    public static function getAdventBattleRaidTotalScoreKey(string $mstAdventBattleId): string
    {
        return "advent_battle:{$mstAdventBattleId}:raid:total_score";
    }

    /**
     * 降臨バトルのランキングAPIのレスポンスデータのキャッシュキー
     * @param string $adventBattleId
     * @return string
     */
    public static function getAdventBattleRankingCacheKey(string $adventBattleId): string
    {
        return "advent_battle:{$adventBattleId}:ranking_cache";
    }

    /**
     * 降臨バトルのランキングのキー
     * @param string $mstAdventBattleId
     * @return string
     */
    public static function getAdventBattleRankingKey(string $mstAdventBattleId): string
    {
        return "advent_battle:{$mstAdventBattleId}:ranking";
    }

    /**
     * プリズム購入履歴用のキー
     * @param string $usrUserId
     * @return string
     */
    public static function getShopPurchaseHistoryKey(string $usrUserId): string
    {
        return "shop:purchaseHistory:{$usrUserId}";
    }

    /**
     * PVPランキングのキー
     * @param string $sysPvpSeasonId
     * @return string
     */
    public static function getPvpRankingKey(string $sysPvpSeasonId): string
    {
        return "pvp:{$sysPvpSeasonId}:ranking";
    }

    public static function getPvpRankingCacheKey(string $sysPvpSeasonId): string
    {
        return "pvp:{$sysPvpSeasonId}:ranking_cache";
    }

    public static function getPvpOpponentStatusKey(string $sysPvpSeasonId, string $myId): string
    {
        return "pvp:{$sysPvpSeasonId}:opponent_status:v1_2_1:{$myId}";
    }

    public static function getPvpOpponentCandidateKey(
        string $sysPvpSeasonId,
        string $rankClassType,
        int $rankClassLevel
    ): string {
        return "pvp:{$sysPvpSeasonId}:opponent_candidate:{$rankClassType}{$rankClassLevel}";
    }

    /**
     * BNIDのアクセストークンAPIから取得したIDのキャッシュキー
     * @param string $code
     * @return string
     */
    public static function getBnidUserIdKey(string $code): string
    {
        return "bnid:{$code}:user_id";
    }

    /**
     * MngMessageBundleのキャッシュキー
     * @param string $language
     * @return string
     */
    public static function getMngMessageBundleKey(string $language): string
    {
        return "mng:mng_message_bundle:{$language}";
    }

    /**
     * MngMasterReleaseVersionのキャッシュキー
     * @return string
     */
    public static function getMngMasterReleaseVersionKey(): string
    {
        return "mng:mng_master_release_version";
    }

    /**
     * MngAssetReleaseVersionのキャッシュキー
     * @param int $platform
     * @return string
     */
    public static function getMngAssetReleaseVersionKey(int $platform): string
    {
        return "mng:mng_asset_release_version:{$platform}";
    }

    /**
     * MngInGameNoticeBundleのキャッシュキー
     * @param string $language
     * @return string
     */
    public static function getMngInGameNoticeBundleKey(string $language): string
    {
        return "mng:mng_in_game_notice_bundle:{$language}";
    }

    /**
     * MngJumpPlusRewardBundleのキャッシュキー
     * @return string
     */
    public static function getMngJumpPlusRewardBundleKey(): string
    {
        return "mng:mng_jump_plus_reward_bundle";
    }

    /**
     * MngClientVersionのキャッシュキー
     * @param int $platform
     * @return string
     */
    public static function getMngClientVersionKey(int $platform): string
    {
        return "mng:mng_client_version:{$platform}";
    }

    /**
     * MngContentCloseのキャッシュキー
     * @return string
     */
    public static function getMngContentCloseKey(): string
    {
        return "mng:mng_content_close";
    }

    /**
     * MngDeletedMyIdのキャッシュキー
     * @return string
     */
    public static function getMngDeletedMyIdKey(): string
    {
        return "mng:mng_deleted_my_id";
    }

    /**
     * ガシャ履歴のキャッシュキー
     * @param string $usrUserId
     * @return string
     */
    public static function getGachaHistoryKey(string $usrUserId): string
    {
        return "gacha_history:user:{$usrUserId}";
    }
}
