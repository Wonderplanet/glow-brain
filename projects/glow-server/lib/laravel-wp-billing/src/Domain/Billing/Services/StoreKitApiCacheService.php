<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;

/**
 * StoreKit API キャッシュを管理するService
 * rate limit対策としてRedisキャッシュを使用
 */
class StoreKitApiCacheService
{
    private const CACHE_KEY_PREFIX = 'storekit_api_cache:';

    public function __construct(
        private CacheClientManager $cacheManager
    ) {
    }

    /**
     * デフォルトキャッシュ有効期限（分）をコンフィグから取得
     *
     * @return int
     */
    private function getDefaultCacheTtlMinutes(): int
    {
        return config('wp_currency.store.app_store.storekit2.api_cache.ttl_minutes', 720);
    }

    /**
     * JWSハッシュでキャッシュを検索
     * 有効期限内のキャッシュのみを返す
     *
     * @param string $jwsHash JWSトークンのSHA256ハッシュ値
     * @return array<string, mixed>|null 有効なキャッシュデータまたはnull
     */
    public function findValidByJwsHash(string $jwsHash): ?array
    {
        $cacheKey = $this->getCacheKey($jwsHash);
        $cacheClient = $this->cacheManager->getCacheClient();
        $cachedData = $cacheClient->get($cacheKey);

        if ($cachedData === null) {
            return null;
        }

        // TTLで管理されているため、取得できれば有効なデータ
        return is_array($cachedData) ? $cachedData : null;
    }

    /**
     * APIレスポンスをキャッシュに保存
     *
     * @param string $jwsHash JWSトークンのSHA256ハッシュ値
     * @param string $transactionId トランザクションID
     * @param string $environment 環境（production/sandbox）
     * @param array<string, mixed> $apiResponse APIレスポンスのペイロード
     * @param int|null $cacheTtlMinutes キャッシュ有効期限（分）。nullの場合はconfig値を使用
     * @return array<string, mixed> 保存されたキャッシュデータ
     */
    public function store(
        string $jwsHash,
        string $transactionId,
        string $environment,
        array $apiResponse,
        ?int $cacheTtlMinutes = null
    ): array {
        $cacheKey = $this->getCacheKey($jwsHash);
        $cacheData = [
            'jws_hash' => $jwsHash,
            'transaction_id' => $transactionId,
            'environment' => $environment,
            'api_response' => $apiResponse,
            'cached_at' => now()->toISOString(),
        ];

        // TTLを秒単位で設定
        $ttlMinutes = $cacheTtlMinutes ?? $this->getDefaultCacheTtlMinutes();
        $ttlSeconds = $ttlMinutes * 60;
        $cacheClient = $this->cacheManager->getCacheClient();
        $cacheClient->set($cacheKey, $cacheData, $ttlSeconds);

        return $cacheData;
    }

    /**
     * 指定されたJWSハッシュのキャッシュを削除
     *
     * @param string $jwsHash JWSトークンのSHA256ハッシュ値
     * @return bool 削除成功（存在しないキャッシュの場合はfalse）
     */
    public function deleteByJwsHash(string $jwsHash): bool
    {
        $cacheKey = $this->getCacheKey($jwsHash);
        $cacheClient = $this->cacheManager->getCacheClient();

        // キャッシュが存在するかチェック
        if ($cacheClient->get($cacheKey) === null) {
            return false;
        }

        $cacheClient->del($cacheKey);
        return true;
    }

    /**
     * JWSハッシュからRedisキャッシュキーを生成
     *
     * @param string $jwsHash JWSトークンのSHA256ハッシュ値
     * @return string Redisキャッシュキー
     */
    private function getCacheKey(string $jwsHash): string
    {
        return self::CACHE_KEY_PREFIX . $jwsHash;
    }
}
