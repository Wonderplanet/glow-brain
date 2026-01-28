<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use Exception;
use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;

/**
 * Apple App Store Server API連携サービス
 * storekit2contextからライブラリ用に移植
 */
class AppStoreServerApiService
{
    /**
     * App Store Server API エンドポイント（本番環境）
     */
    public const API_BASE_URL_PRODUCTION = 'https://api.storekit.itunes.apple.com';

    /**
     * App Store Server API エンドポイント（サンドボックス環境）
     */
    public const API_BASE_URL_SANDBOX = 'https://api.storekit-sandbox.itunes.apple.com';

    public function __construct(
        private JwsService $jwsService
    ) {
    }

    /**
     * App Store Server API: トランザクション情報取得
     * @param string $transactionId
     * @param string $environment AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION
     *                            or AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
     * @param string $expectedProductId 期待するproductId（履歴APIフォールバック時のチェック用）
     * @return array<string, mixed>
     * @throws WpBillingException
     */
    public function lookup(string $transactionId, string $environment, string $expectedProductId): array
    {
        $token = $this->getAppStoreToken($environment);
        $endpoint = $this->getApiBaseUrl($environment) . "/inApps/v1/transactions/{$transactionId}";

        $headers = [
            "Authorization: Bearer {$token}",
            'Content-Type: application/json',
        ];

        $result = $this->executeHttpRequest($endpoint, $headers);
        $response = $result['response'];
        $httpCode = $result['httpCode'];

        switch ($httpCode) {
            case 200:
                $body = json_decode($response, true);
                if ($body === null) {
                    throw new WpBillingException(
                        'Invalid JSON response from App Store Server API',
                        ErrorCode::APPSTORE_API_RESPONSE_ERROR
                    );
                }
                $signedTransactionInfo = $body['signedTransactionInfo'] ?? null;
                if (!$signedTransactionInfo) {
                    throw new WpBillingException(
                        'signedTransactionInfo is missing in API response',
                        ErrorCode::APPSTORE_API_RESPONSE_ERROR
                    );
                }
                // App Store Server APIから取得したJWSはApple署名済み
                return $this->jwsService->decodeStoreServerJws($signedTransactionInfo);

            case 404:
                throw new WpBillingException("Transaction not found in App Store records (HTTP {$httpCode})");

            case 429:
                // Rate Limit時のフォールバック設定確認
                // getTransactionHistoryは履歴全体を取得するため、lookupより重い処理となる
                // パフォーマンス影響を考慮し、設定により有効/無効を制御可能とする
                // また、両API同時にRate Limitとなるリスクや運用時の柔軟性も考慮
                $enableHistoryFallback = config('wp_currency.store.app_store.storekit2.enable_history_fallback', false);
                if ($enableHistoryFallback) {
                    // getTransactionHistoryでフォールバック試行
                    try {
                        $historyData = $this->getTransactionHistory($transactionId, $environment);
                        // 履歴の最新データを返す（lookupと同等の情報）
                        // getTransactionHistory内で既にソート済みなので、0番目が最新データ
                        if (count($historyData) > 0) {
                            $latestTransaction = $historyData[0];

                            // productIdのチェック
                            $actualProductId = $latestTransaction['productId'] ?? null;
                            if ($actualProductId !== $expectedProductId) {
                                throw new WpBillingException(
                                    "Product ID mismatch in history fallback: " .
                                    "expected {$expectedProductId}, got {$actualProductId}",
                                    ErrorCode::APPSTORE_API_RESPONSE_ERROR
                                );
                            }

                            return $latestTransaction; // 最新のトランザクション情報
                        }
                        // 履歴が空の場合はtryブロックを抜けて、最終的に元のRate Limitエラーを投げる
                    } catch (Exception $historyException) {
                        // フォールバックも失敗した場合は元のRate Limitエラーをthrow
                        throw new WpBillingException(
                            "App Store Server API rate limit exceeded and history fallback failed (HTTP {$httpCode})",
                            ErrorCode::APPSTORE_API_RATE_LIMIT_ERROR,
                            $historyException
                        );
                    }
                }

                throw new WpBillingException(
                    "App Store Server API rate limit exceeded (HTTP {$httpCode})",
                    ErrorCode::APPSTORE_API_RATE_LIMIT_ERROR
                );

            default:
                throw new WpBillingException("App Store Server API error (HTTP {$httpCode})");
        }
    }

    /**
     * App Store Server API: トランザクション履歴取得
     * @param string $transactionId
     * @param string $environment AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION
     *                            or AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
     * @return array<int, array<string, mixed>>
     * @throws WpBillingException
     */
    public function getTransactionHistory(string $transactionId, string $environment): array
    {
        $token = $this->getAppStoreToken($environment);
        $endpoint = $this->getApiBaseUrl($environment) . "/inApps/v2/history/{$transactionId}";

        $headers = [
            "Authorization: Bearer {$token}",
            'Content-Type: application/json',
        ];

        $result = $this->executeHttpRequest($endpoint, $headers);
        $response = $result['response'];
        $httpCode = $result['httpCode'];

        switch ($httpCode) {
            case 200:
                $body = json_decode($response, true);
                $signedTransactions = $body['signedTransactions'] ?? [];
                $history = [];
                foreach ($signedTransactions as $signedTransaction) {
                    $history[] = $this->jwsService->decodeStoreServerJws($signedTransaction);
                }
                // 取り消されていないトランザクションを抽出し、日付でソートして最新を0番目にする
                return $this->filterAndSortHistoryByDateDesc($history);

            case 429:
                throw new WpBillingException(
                    'Too Many Requests.',
                    ErrorCode::APPSTORE_API_RATE_LIMIT_ERROR
                );

            default:
                throw new WpBillingException(
                    'App Store Server API error: ' . $httpCode,
                    ErrorCode::APPSTORE_API_COMMUNICATION_ERROR
                );
        }
    }

    /**
     * 外部APIからApp Store用JWTトークンを取得
     * @param string $externalTokenUrl
     * @return string|null
     */
    protected function fetchExternalToken(string $externalTokenUrl): ?string
    {
        $result = $this->executeHttpRequest($externalTokenUrl, [], 30);
        $response = $result['response'];
        $httpCode = $result['httpCode'];

        if ($response === '' || $httpCode !== 200) {
            return null;
        }
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * App Store Server API用のJWTトークンを取得
     * @param string $environment AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION
     *                            or AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
     * @return string
     * @throws WpBillingException
     */
    protected function getAppStoreToken(string $environment): string
    {
        $externalTokenUrl = config('wp_currency.store.app_store.storekit2.external_token_url');

        if ($externalTokenUrl) {
            $token = $this->fetchExternalToken($externalTokenUrl);
            if (!$token) {
                throw new WpBillingException(
                    '外部トークン取得API error or トークンが取得できませんでした',
                    ErrorCode::EXTERNAL_API_COMMUNICATION_ERROR
                );
            }
            return $token;
        }
        // 外部トークンURL未設定時は自前でJWT生成
        return $this->jwsService->createJwt($environment);
    }

    /**
     * App Store Server APIのベースURLを取得
     * @param string $environment AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION
     *                            or AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
     * @return string
     * @throws WpBillingException 不正な環境値の場合
     */
    public function getApiBaseUrl(string $environment): string
    {
        return AppStoreEnvironmentValidator::isSandbox($environment)
            ? self::API_BASE_URL_SANDBOX
            : self::API_BASE_URL_PRODUCTION;
    }

    /**
     * HTTP リクエストを実行
     *
     * @param string $url リクエストURL
     * @param array<int, string> $headers HTTPヘッダー配列
     * @param int $timeout タイムアウト秒数
     * @return array{response: string, httpCode: int} レスポンスとHTTPステータスコード
     * @throws WpBillingException HTTP通信エラーの場合
     */
    protected function executeHttpRequest(string $url, array $headers = [], int $timeout = 30): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $curlError = curl_error($ch);
            curl_close($ch);
            throw new WpBillingException(
                "HTTP request failed to {$url}: {$curlError}",
                ErrorCode::EXTERNAL_API_COMMUNICATION_ERROR
            );
        }

        curl_close($ch);

        return [
            'response' => $response,
            'httpCode' => $httpCode,
        ];
    }

    /**
     * 取り消されていないトランザクション履歴を抽出し、日付の降順（新しい順）でソート
     *
     * @param array<int, array<string, mixed>> $historyData
     * @return array<int, array<string, mixed>>
     */
    protected function filterAndSortHistoryByDateDesc(array $historyData): array
    {
        // 取り消されていないトランザクションのみに絞り込み
        $validTransactions = array_filter($historyData, function ($transaction) {
            return !isset($transaction['revocationDate']);
        });

        // 日付フィールドでソート（降順）
        usort($validTransactions, function ($a, $b) {
            // App Store Server APIのレスポンスでは、日付は 'purchaseDate' フィールドに含まれる
            // ミリ秒単位のタイムスタンプとして格納されている
            // purchaseDateが存在しない場合、スペースシップ演算子で自動的にエラーになる
            return $b['purchaseDate'] <=> $a['purchaseDate'];
        });

        return $validTransactions;
    }
}
