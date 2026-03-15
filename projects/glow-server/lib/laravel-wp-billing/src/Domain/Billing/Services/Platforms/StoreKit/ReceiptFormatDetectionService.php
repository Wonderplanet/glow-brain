<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

/**
 * StoreKit1とStoreKit2のレシート形式を判定するサービス
 */
class ReceiptFormatDetectionService
{
    public const RECEIPT_TYPE_STOREKIT1 = 'storekit1';
    public const RECEIPT_TYPE_STOREKIT2 = 'storekit2';
    public const RECEIPT_TYPE_UNKNOWN = 'unknown';

    /**
     * レシートがStoreKit2の形式（JWS）かどうかを判定
     * JWS形式（header.payload.signature）の検証とStoreKit2特有の構造チェック
     *
     * @param string $receipt 判定対象のレシート文字列
     * @return bool StoreKit2形式の場合true
     */
    private function isStoreKit2Format(string $receipt): bool
    {
        // StoreKit2のsignedTransactionInfoはJWS形式
        // JWSは "header.payload.signature" の3つの部分がbase64urlでエンコードされている
        $parts = explode('.', $receipt);

        if (count($parts) !== 3) {
            return false;
        }

        // 各部分がbase64url形式かチェック
        foreach ($parts as $part) {
            if (!$this->isValidBase64Url($part)) {
                return false;
            }
        }

        // ヘッダー部分をデコードしてJWS特有の構造をチェック
        try {
            $headerJson = base64_decode(strtr($parts[0], '-_', '+/'));
            $header = json_decode($headerJson, true);

            // JWSヘッダーに必要な要素があるかチェック
            if (!is_array($header)) {
                return false;
            }

            // StoreKit2のJWSは通常ES256アルゴリズムとx5c証明書チェーンを持つ
            if (!isset($header['alg']) || !isset($header['x5c'])) {
                return false;
            }

            // アルゴリズムがES256であることを確認
            if ($header['alg'] !== 'ES256') {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * レシートがStoreKit1の形式（JSON）かどうかを判定
     * JSON構造とStoreKit1特有のフィールド（Store, Payload）をチェック
     *
     * @param string $receipt 判定対象のレシート文字列
     * @return bool StoreKit1形式の場合true
     */
    private function isStoreKit1Format(string $receipt): bool
    {
        // StoreKit1のレシートはJSON形式
        $receiptJson = json_decode($receipt, true);

        if (!is_array($receiptJson)) {
            return false;
        }

        // StoreKit1レシートの特徴的な構造をチェック
        // 現在のAppStorePlatformServiceのisAppStoreReceipt()ロジックを使用
        return $this->hasStoreKit1Structure($receiptJson);
    }

    /**
     * レシート形式を判定して適切なタイプを返す
     * StoreKit1/2の自動判定を行うメインメソッド
     *
     * @param string $receipt 判定対象のレシート文字列
     * @return string 定数（RECEIPT_TYPE_STOREKIT1|RECEIPT_TYPE_STOREKIT2|RECEIPT_TYPE_UNKNOWN）
     */
    public function detectReceiptType(string $receipt): string
    {
        if ($this->isStoreKit2Format($receipt)) {
            return self::RECEIPT_TYPE_STOREKIT2;
        }

        if ($this->isStoreKit1Format($receipt)) {
            return self::RECEIPT_TYPE_STOREKIT1;
        }

        return self::RECEIPT_TYPE_UNKNOWN;
    }

    /**
     * Base64URLエンコーディングが有効かチェック
     * JWS形式で使用されるBase64URL（RFC 4648）の形式検証
     *
     * @param string $data 検証対象の文字列
     * @return bool 有効なBase64URL形式の場合true
     */
    private function isValidBase64Url(string $data): bool
    {
        // 空文字列は無効
        if ($data === '') {
            return false;
        }

        // Base64URLは A-Z, a-z, 0-9, -, _ のみを使用
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $data)) {
            return false;
        }

        // デコードが成功するかチェック（より寛容に）
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        return $decoded !== false && $decoded !== '';
    }

    /**
     * StoreKit1レシートの構造を持つかチェック
     * Store='AppleAppStore'とPayloadフィールドの存在を確認
     *
     * @param array<string, mixed> $receiptJson パース済みのJSONレシートデータ
     * @return bool StoreKit1の必要構造を持つ場合true
     */
    private function hasStoreKit1Structure(array $receiptJson): bool
    {
        // StoreKit1レシートに必要なフィールドをチェック
        // StoreKit1ServiceのisAppStoreReceipt()メソッドと同じロジック
        if (!isset($receiptJson['Store']) || $receiptJson['Store'] !== 'AppleAppStore') {
            return false;
        }

        return isset($receiptJson['Payload']) &&
               is_string($receiptJson['Payload']) &&
               $receiptJson['Payload'] !== '';
    }
}
