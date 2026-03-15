<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use WonderPlanet\Domain\Billing\Services\StoreKitApiCacheService as StoreKitApiCacheStorageService;

/**
 * StoreKit API キャッシュサービス
 * rate limit対策としてJWSハッシュベースでAPIレスポンスをキャッシュ
 */
class StoreKitApiCacheService
{
    public function __construct(
        private StoreKitApiCacheStorageService $cacheRepository,
        private JwsService $jwsService
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
     * キャッシュからAPIレスポンスを取得
     *
     * @param string $signedTransactionInfo JWSトークン
     * @return array<string, mixed>|null キャッシュされたAPIレスポンスまたはnull
     */
    public function getCachedResponse(string $signedTransactionInfo): ?array
    {
        $jwsHash = $this->jwsService->calculateJwsHash($signedTransactionInfo);
        $cachedData = $this->cacheRepository->findValidByJwsHash($jwsHash);

        return $cachedData['api_response'] ?? null;
    }

    /**
     * APIレスポンスをキャッシュに保存
     *
     * @param string $signedTransactionInfo JWSトークン
     * @param string $transactionId トランザクションID
     * @param string $environment 環境（production/sandbox）
     * @param array<string, mixed> $apiResponse APIレスポンス
     * @param int|null $cacheTtlMinutes キャッシュ有効期限（分）。nullの場合はconfig値を使用
     * @return void
     */
    public function cacheResponse(
        string $signedTransactionInfo,
        string $transactionId,
        string $environment,
        array $apiResponse,
        ?int $cacheTtlMinutes = null
    ): void {
        $jwsHash = $this->jwsService->calculateJwsHash($signedTransactionInfo);

        $this->cacheRepository->store(
            $jwsHash,
            $transactionId,
            $environment,
            $apiResponse,
            $cacheTtlMinutes ?? $this->getDefaultCacheTtlMinutes()
        );
    }

    /**
     * 指定されたJWSのキャッシュを無効化
     *
     * @param string $signedTransactionInfo JWSトークン
     * @return bool 削除成功
     */
    public function invalidateCache(string $signedTransactionInfo): bool
    {
        $jwsHash = $this->jwsService->calculateJwsHash($signedTransactionInfo);
        return $this->cacheRepository->deleteByJwsHash($jwsHash);
    }
}
