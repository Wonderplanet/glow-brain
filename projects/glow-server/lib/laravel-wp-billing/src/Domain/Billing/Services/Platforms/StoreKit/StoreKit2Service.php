<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use Exception;
use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Utils\StoreUtility;

/**
 * StoreKit2専用サービス
 * storekit2contextからの移植版
 */
class StoreKit2Service
{
    public function __construct(
        private JwsService $jwsService,
        private AppStoreServerApiService $apiService,
        private StoreKitApiCacheService $cacheService
    ) {
    }

    /**
     * StoreKit2のsignedTransactionInfoを検証してトランザクション情報を取得
     * JWS検証、Bundle ID確認、App Store Server API照合を行う
     * rate limit対策として同一JWSハッシュのAPIレスポンスをキャッシュから取得
     *
     * @param string $signedTransactionInfo StoreKit2のJWSトークン
     * @return array<string, mixed> 検証済みトランザクションペイロード
     * @throws WpBillingException JWS検証、Bundle ID不一致、API通信エラーの場合
     */
    public function verifyTransaction(string $signedTransactionInfo): array
    {
        try {
            // 1. JWS検証
            $payload = $this->jwsService->verify($signedTransactionInfo);

            // 2. transactionId必須バリデーション
            if (
                !isset($payload['transactionId']) ||
                !is_string($payload['transactionId']) ||
                trim($payload['transactionId']) === ''
            ) {
                throw new WpBillingException(
                    'transactionId is required in JWS payload',
                    ErrorCode::INVALID_RECEIPT
                );
            }

            // 3. bundle_id検証
            $this->validateBundleId($payload);

            // 4. キャッシュチェック（rate limit対策）
            $cachedResponse = $this->cacheService->getCachedResponse($signedTransactionInfo);
            if ($cachedResponse !== null) {
                // キャッシュヒット: APIコールをスキップして結果を返す
                return $cachedResponse;
            }

            // 5. App Store Server APIで検証
            // テスト環境分岐は削除。テスト時はapiServiceをモックすること。
            try {
                $apiResponse = $this->apiService->lookup(
                    $payload['transactionId'],
                    $payload['environment'],
                    $payload['productId']
                );

                // API成功時、キャッシュに保存（rate limit対策）
                $this->cacheService->cacheResponse(
                    $signedTransactionInfo,
                    $payload['transactionId'],
                    $payload['environment'],
                    $apiResponse
                );

                // App Store Server APIが200を返せばOK。レスポンス内容は現状未使用。
                return $payload;
            } catch (Exception $apiException) {
                // App Store Server API エラーは別途処理
                throw new WpBillingException(
                    'App Store Server API verification failed: ' . $apiException->getMessage(),
                    ErrorCode::APPSTORE_API_COMMUNICATION_ERROR,
                    $apiException
                );
            }
        } catch (WpBillingException $e) {
            // 既にWpBillingExceptionの場合はそのまま再スロー
            throw $e;
        } catch (Exception $e) {
            // JWS関連のエラーを分類
            if (strpos($e->getMessage(), 'JWS') !== false || strpos($e->getMessage(), 'signature') !== false) {
                throw new WpBillingException(
                    'JWS signature verification failed: ' . $e->getMessage(),
                    ErrorCode::APPSTORE_JWS_SIGNATURE_INVALID,
                    $e
                );
            }

            // その他の予期しないエラー
            throw new WpBillingException(
                'StoreKit2 transaction verification failed: ' . $e->getMessage(),
                ErrorCode::INVALID_RECEIPT,
                $e
            );
        }
    }

    /**
     * ペイロードから既存のStoreReceiptと互換性のある情報を抽出
     * StoreKit2形式をStoreKit1形式に変換してレガシーコードとの互換性を保つ
     *
     * @param array<string, mixed> $payload 検証済みStoreKit2ペイロード
     * @return array<string, mixed> StoreKit1形式の構造化レシート情報
     */
    public function extractReceiptInfo(array $payload): array
    {
        // StoreKit2のJWSペイロードをStoreKit1形式に変換して返す
        return StoreKit2ToLegacyReceiptConverter::convert($payload);
    }

    /**
     * JWSトークンからサンドボックス環境かどうかを判定（API呼び出し前に実行可能）
     * ペイロードのenvironmentフィールドを基に判定
     *
     * @param string $signedTransactionInfo StoreKit2のJWSトークン
     * @return bool true: サンドボックス環境, false: 本番環境
     * @throws WpBillingException JWS形式不正またはenvironmentフィールド不足の場合
     */
    public function isSandboxEnvironmentFromJws(string $signedTransactionInfo): bool
    {
        // JWS形式の検証（basicな形式チェック）
        $parts = explode('.', $signedTransactionInfo);
        if (count($parts) !== 3) {
            throw new WpBillingException(
                'Invalid JWS format: expected 3 parts separated by dots',
                ErrorCode::INVALID_RECEIPT
            );
        }

        // ペイロード部分をデコード
        $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($payloadJson === false) {
            throw new WpBillingException(
                'JWS payload JSON decode failed',
                ErrorCode::INVALID_RECEIPT
            );
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            throw new WpBillingException(
                'JWS payload JSON decode failed',
                ErrorCode::INVALID_RECEIPT
            );
        }

        // environmentフィールドを確認
        if (!isset($payload['environment']) || !is_string($payload['environment']) || $payload['environment'] === '') {
            throw new WpBillingException(
                'JWS payload environment field is missing or invalid',
                ErrorCode::INVALID_RECEIPT
            );
        }

        return AppStoreEnvironmentValidator::isSandbox($payload['environment']);
    }

    /**
     * JWSトークンに基づいて適切なAPIエンドポイントを取得
     * 環境判定してサンドボックスまたは本番のAPIエンドポイントを返す
     *
     * @param string $signedTransactionInfo StoreKit2のJWSトークン
     * @return string App Store Server APIのベースURL
     * @throws WpBillingException JWS解析エラーの場合
     */
    public function getApiUrlForTransaction(string $signedTransactionInfo): string
    {
        $isSandbox = $this->isSandboxEnvironmentFromJws($signedTransactionInfo);

        return $isSandbox
            ? AppStoreServerApiService::API_BASE_URL_SANDBOX
            : AppStoreServerApiService::API_BASE_URL_PRODUCTION;
    }

    /**
     * Bundle IDの検証
     * JWSペイロードのBundle IDと設定値を比較検証
     *
     * @param array<string, mixed> $payload JWSペイロード
     * @throws WpBillingException Bundle IDまたはenvironmentが不正/不一致の場合
     */
    private function validateBundleId(array $payload): void
    {
        // bundleIdの存在と空文字列チェックを明示的に
        if (!isset($payload['bundleId']) || !is_string($payload['bundleId']) || $payload['bundleId'] === '') {
            throw new WpBillingException(
                'bundleId is required in JWS payload',
                ErrorCode::INVALID_RECEIPT
            );
        }
        $receiptBundleId = $payload['bundleId'];

        // environment必須チェック
        if (!isset($payload['environment']) || !is_string($payload['environment']) || $payload['environment'] === '') {
            throw new WpBillingException(
                'environment is required in JWS payload',
                ErrorCode::INVALID_RECEIPT
            );
        }

        // 環境に応じて期待されるbundle_idを取得
        $expectedBundleId = AppStoreEnvironmentValidator::isSandbox($payload['environment'])
            ? StoreUtility::getSandboxBundleId()
            : StoreUtility::getProductionBundleId();

        if ($expectedBundleId === '') {
            throw new WpBillingException(
                "Expected bundle ID is not configured for environment: " . $payload['environment'],
                ErrorCode::INVALID_RECEIPT
            );
        }

        if ($receiptBundleId !== $expectedBundleId) {
            throw new WpBillingException(
                sprintf(
                    'Bundle ID mismatch. Expected: %s, Received: %s',
                    $expectedBundleId,
                    $receiptBundleId
                ),
                ErrorCode::INVALID_RECEIPT
            );
        }
    }
}
