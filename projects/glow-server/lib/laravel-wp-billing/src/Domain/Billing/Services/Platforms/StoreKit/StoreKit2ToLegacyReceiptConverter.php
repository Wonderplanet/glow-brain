<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;

/**
 * StoreKit2のJWSペイロードをStoreKit1形式（receipt/in_app構造）に変換するユーティリティ
 *
 * 【背景】
 * iOS 15以降、Appleは新しいStoreKit2 APIを導入したが、データ構造が大幅に変更された。
 * 既存システムがStoreKit1の「receipt.in_app[]」配列構造に依存しているため、
 * 後方互換性を保つための変換レイヤーとして本クラスを提供する。
 *
 * 【主な変換内容】
 * - 単一トランザクション構造 → receipt.in_app[0]への配置
 * - purchaseDate(エポックミリ秒またはISO8601) → purchase_date(ISO8601) + purchase_date_ms
 * - フラット構造 → 階層化されたレシート構造
 *
 * 【purchaseDate対応について】
 * Appleの公式ドキュメントにpurchaseDateの明確なフォーマット記載がないため、
 * 実際の運用で確認されているエポックミリ秒に加え、将来的な仕様変更に備えて
 * ISO8601文字列にも対応している。
 */
class StoreKit2ToLegacyReceiptConverter
{
    /**
     * StoreKit2 JWSペイロードをStoreKit1形式に変換
     *
     * 【変換の目的】
     * - 既存のStoreReceiptクラスとの互換性維持
     * - StoreKit1からStoreKit2への段階的移行支援
     * - レガシーコードの大幅な修正なしでのStoreKit2対応
     *
     * 【必須入力フィールド】
     * - environment: 'Production' または 'Sandbox'（Apple規定値）
     * - purchaseDate: エポックミリ秒(int)またはISO8601文字列(string)
     *   ※ Appleドキュメントに明確な記載がないため、実運用で確認されている
     *     エポックミリ秒と、将来的な変更に備えたISO8601の両方に対応
     *
     * 【出力フォーマット】
     * StoreKit1の標準的なレシート構造：
     * {
     *   "receipt": {
     *     "bundle_id": "...",
     *     "in_app": [{ transaction_data }]
     *   },
     *   "environment": "Production|Sandbox"
     * }
     *
     * @param array<string, mixed> $payload StoreKit2のJWSペイロード
     * @return array<string, mixed> StoreKit1形式のレシート配列
     * @throws WpBillingException 必須フィールド不足または型不正の場合
     */
    public static function convert(array $payload): array
    {
        // 必須フィールドの存在チェック
        // environment: Apple App Store環境識別子（'Production' または 'Sandbox'）
        // 注意：upstream（AppStoreEnvironmentValidator）で既に検証済みだが、
        // 本メソッドの独立性とテスト可能性のため再度チェックを行う
        if (!isset($payload['environment']) || !is_string($payload['environment']) || $payload['environment'] === '') {
            throw new WpBillingException('StoreKit2 payloadにenvironmentが存在しません', ErrorCode::INVALID_RECEIPT);
        }

        // purchaseDate: Apple StoreKit2でのpurchaseDate（複数フォーマット対応）
        // 形式1：エポックミリ秒（整数） 例：1640995200000 → 2022-01-01T00:00:00.000Z
        // 形式2：ISO8601文字列 例："2022-01-01T00:00:00.000Z"
        //
        // 【対応理由】
        // Appleの公式ドキュメントにpurchaseDateの明確なフォーマット記載がない。
        // 実際の運用では主にエポックミリ秒が送信されているが、
        // 将来的な仕様変更やエッジケースに備えてISO8601形式にも対応。
        if (!isset($payload['purchaseDate'])) {
            throw new WpBillingException('StoreKit2 payloadにpurchaseDateが存在しません', ErrorCode::INVALID_RECEIPT);
        }

        $purchaseDate = $payload['purchaseDate'];

        // purchaseDateの形式判定と正規化
        if (is_int($purchaseDate)) {
            // 形式1：エポックミリ秒 → そのまま使用
            $purchaseDateMs = (string)$purchaseDate;
            $purchaseDateIso = date('c', intval($purchaseDate / 1000));
        } elseif (is_string($purchaseDate) && $purchaseDate !== '') {
            // 形式2：ISO8601文字列 → エポックミリ秒に変換
            $timestamp = strtotime($purchaseDate);
            if ($timestamp === false) {
                throw new WpBillingException('StoreKit2 purchaseDateのISO8601形式が不正です', ErrorCode::INVALID_RECEIPT);
            }
            $purchaseDateMs = (string)($timestamp * 1000);
            $purchaseDateIso = date('c', $timestamp);
        } else {
            throw new WpBillingException(
                'StoreKit2 purchaseDateは数値型またはISO8601文字列である必要があります',
                ErrorCode::INVALID_RECEIPT
            );
        }

        // StoreKit1形式のレシート構造を生成
        // 既存のStoreReceiptクラス（receipt.in_app[0]形式）との完全互換性を確保
        return [
            'receipt' => [
                // アプリケーションのバンドルID（例：com.example.myapp）
                'bundle_id' => $payload['bundleId'] ?? '',

                // in_app配列：StoreKit1では複数のトランザクションを配列で管理
                // StoreKit2の単一トランザクションを配列の最初の要素として配置
                'in_app' => [
                    [
                        // 商品ID：App Store Connectで定義したアプリ内課金アイテムの識別子
                        'product_id' => $payload['productId'] ?? '',

                        // トランザクションID：Appleが発行する一意の取引識別子
                        'transaction_id' => $payload['transactionId'] ?? '',

                        // 購入日時（ISO8601形式）：既存システムでの可読性重視
                        // 注意：purchaseDateの入力形式に関わらず、常にISO8601形式で出力
                        'purchase_date' => $purchaseDateIso,

                        // 購入日時（エポックミリ秒文字列）：StoreKit1との完全互換性
                        // 注意：数値ではなく文字列として格納（StoreKit1慣例に従う）
                        'purchase_date_ms' => $purchaseDateMs,

                        // 将来的な拡張ポイント：
                        // expires_date, expires_date_ms (サブスクリプション)
                        // is_trial_period, is_in_intro_offer_period (トライアル関連)
                        // web_order_line_item_id (Web経由購入) など
                    ],
                ],
            ],

            // 実行環境：Apple App Store環境識別子
            // StoreKit1/StoreKit2共通フィールド（そのまま引き継ぎ）
            'environment' => $payload['environment'],
        ];
    }
}
