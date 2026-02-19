<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Utils\StoreUtility;

/**
 * StoreKit1専用サービス
 * AppStorePlatformServiceから移植して分離
 */
class StoreKit1Service
{
    public const RESPONSE_APPLE_OK      = 0;
    public const RESPONSE_APPLE_SANDBOX = 21007;

    /**
     * レシート確認用URL
     */
    public const RECEIPT_VERIFICATION_SERVER = "https://buy.itunes.apple.com/verifyReceipt";
    /**
     * レシート確認用URL (サンドボックス用)
     */
    public const RECEIPT_VERIFICATION_SERVER_SANDBOX = "https://sandbox.itunes.apple.com/verifyReceipt";

    public const HTTP_STATUS_OK = 200;

    /**
     * StoreKit1レシートの検証
     *
     * @param string $productId
     * @param string $receipt
     * @return array<string, mixed> 検証結果
     * @throws WpBillingException
     */
    public function verifyReceipt(string $productId, string $receipt): array
    {
        // レシートをjsonデコードする
        $receiptJson = json_decode($receipt, true);
        if (is_null($receiptJson)) {
            throw new WpBillingException(
                'invalid receipt, json decode failed.',
                ErrorCode::INVALID_RECEIPT
            );
        }

        if (!$this->isAppStoreReceipt($receiptJson)) {
            // AppStoreのレシートでなければ無効
            throw new WpBillingException(
                'invalid receipt, This receipt is not AppStore receipt.',
                ErrorCode::INVALID_RECEIPT
            );
        }

        // AppStoreへ問い合わせ
        return $this->verifyReceiptToAppStoreApi($receiptJson['Payload']);
    }

    /**
     * レシートJSONデータがAppStoreのレシートであるかを判定する
     *
     * @param array<mixed> $receiptJson クライアントから送信されたレシートJSON
     * @return bool
     */
    private function isAppStoreReceipt(array $receiptJson): bool
    {
        // StoreがAppleAppStoreであること
        if ($receiptJson['Store'] !== 'AppleAppStore') {
            return false;
        }

        return true;
    }

    /**
     * ストアのAPIでレシートの検証を行う
     *
     * APIの詳細は次のURL参照
     * @see https://developer.apple.com/documentation/appstorereceipts/verifyreceipt
     *
     * @param string $receiptPayload  Payloadに指定されているBase64文字列
     * @return array<mixed> ストアへの問い合わせ結果
     */
    private function verifyReceiptToAppStoreApi(string $receiptPayload): array
    {
        $encodedReceiptData = json_encode(['receipt-data' => $receiptPayload]);

        // bundle IDの取得確認
        //  設定からbundle idが取得できていなければエラー
        $bundleId = StoreUtility::getProductionBundleId();
        $sandboxBundleId = StoreUtility::getSandboxBundleId();
        if ($bundleId === '') {
            throw new WpBillingException(
                'invalid bundle id. bundle id is not set.',
                ErrorCode::APPSTORE_BUNDLE_ID_NOT_SET
            );
        }
        // sandboxも確認
        if ($sandboxBundleId === '') {
            throw new WpBillingException(
                'invalid bundle id. sandbox bundle id is not set.',
                ErrorCode::APPSTORE_BUNDLE_ID_NOT_SET
            );
        }

        // 本番への問い合わせ
        $response = $this->accessStore(
            self::RECEIPT_VERIFICATION_SERVER,
            $encodedReceiptData
        );
        $status = $response['status'] ?? -1;

        // サンドボックスへの問い合わせ
        if ($status === self::RESPONSE_APPLE_SANDBOX) {
            $response = $this->accessStore(
                self::RECEIPT_VERIFICATION_SERVER_SANDBOX,
                $encodedReceiptData
            );
            $status = $response['status'] ?? -1;
            $bundleId = $sandboxBundleId;
        }

        // statusがOKでなければエラー
        if ($status !== self::RESPONSE_APPLE_OK) {
            throw new WpBillingException("invalid status. status:$status", ErrorCode::APPSTORE_RESPONSE_STATUS_NOT_OK);
        }

        $resultReceipt = $response['receipt'];
        if ($resultReceipt['bundle_id'] !== $bundleId) {
            // バンドルIDがレシートと一致しない
            throw new WpBillingException(
                "invalid receipt. bundle_id:{$resultReceipt['bundle_id']}",
                ErrorCode::APPSTORE_BUNDLE_ID_NOT_MATCH
            );
        }

        return $response;
    }

    /**
     * Appleのエンドポイントでレシートを検証する
     *
     * ※curlによる通信が発生する
     *
     * @param string $server
     * @param string $receiptData
     * @return array<mixed> ストアへの問い合わせ結果
     */
    private function accessStore(string $server, string $receiptData): array
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $server);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $receiptData);
        $encodedResponse = curl_exec($curlHandle);
        $curlErrorNo = curl_errno($curlHandle);
        $curlErrorMsg = curl_error($curlHandle);
        $curlInfoTotalTime = curl_getinfo($curlHandle, CURLINFO_TOTAL_TIME);
        $httpCode = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);
        curl_close($curlHandle);

        if (!$encodedResponse) {
            throw new WpBillingException(
                "failed to connect URL:$server errno:{$curlErrorNo} error:{$curlErrorMsg} time:{$curlInfoTotalTime}"
            );
        }

        if ($httpCode !== self::HTTP_STATUS_OK) {
            // httpステータスコード異常
            throw new WpBillingException(
                "failed to connect URL:$server status code:$httpCode errno:{$curlErrorNo} " .
                    "error:{$curlErrorMsg} time:{$curlInfoTotalTime}"
            );
        }

        return json_decode($encodedResponse, true);
    }

    /**
     * StoreKit1のレスポンスから既存のStoreReceiptと互換性のある情報を抽出
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    public function extractReceiptInfo(array $response): array
    {
        return $response; // StoreKit1は既存フォーマットをそのまま使用
    }
}
