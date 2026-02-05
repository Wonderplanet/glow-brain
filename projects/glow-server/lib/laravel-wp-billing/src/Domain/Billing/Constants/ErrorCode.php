<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Constants;

/**
 * 課金基盤で使用するエラーコードを定義するクラス
 *
 * ※通貨基盤側のエラーコードはcurrencyを参照すること
 */
class ErrorCode
{
    /** @var int ショップ情報レコードが存在していない */
    public const SHOP_INFO_NOT_FOUND = 1;

    /** @var int 不正なレシート */
    public const INVALID_RECEIPT = 10;

    /** @var int 使用できない環境 */
    public const INVALID_ENVIRONMENT = 11;

    /** @var int 未対応の課金プラットフォーム */
    public const UNSUPPORTED_BILLING_PLATFORM = 12;

    /** @var int 未対応のレシート */
    public const UNSUPPORTED_RECEIPT = 13;

    /** @var int 許可レコード(Allowance)が不正 */
    public const INVALID_ALLOWANCE = 14;

    /** @var int レシートのユニークIDが重複している (すでに購入済みレシート) */
    public const DUPLICATE_RECEIPT_UNIQUE_ID = 15;

    /** @var int ショップ購入履歴のレコードが存在しない */
    public const USR_STORE_PRODUCT_HISTORY_NOT_FOUND = 16;

    /** @var int ショップ購入履歴テーブルのusr_user_idと一致しない */
    public const UNMATCHED_USR_STORE_PRODUCT_HISTORY_USER_ID = 17;

    /** @var int トランザクション終了 */
    public const BILLING_TRANSACTION_END = 99;

    // マスタデータの検証
    /** @var int allowanceとopr_productが不整合 */
    public const ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH = 100;

    /** @var int allowanceとmst_stor_productが不整合 */
    public const ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH = 101;

    /** @var int OprProductが見つからない */
    public const OPR_PRODUCT_NOT_FOUND = 102;

    /** @var int MstStoreProductが見つからない */
    public const MST_STORE_PRODUCT_NOT_FOUND = 103;

    // AppStoreのエラーコード
    /** @var int AppStoreからの応答ステータスがOKではない */
    public const APPSTORE_RESPONSE_STATUS_NOT_OK = 1001;

    /** @var int AppStoreのバンドルIDが一致しない */
    public const APPSTORE_BUNDLE_ID_NOT_MATCH = 1002;

    /** @var int 設定からbundle idが取得できない */
    public const APPSTORE_BUNDLE_ID_NOT_SET = 1003;

    /** @var int StoreKit2 JWS署名検証エラー */
    public const APPSTORE_JWS_SIGNATURE_INVALID = 1004;

    /** @var int StoreKit2 JWS形式エラー */
    public const APPSTORE_JWS_FORMAT_INVALID = 1005;

    /** @var int App Store Server API通信エラー */
    public const APPSTORE_API_COMMUNICATION_ERROR = 1006;

    /** @var int App Store Server APIレスポンスエラー */
    public const APPSTORE_API_RESPONSE_ERROR = 1007;

    /** @var int 外部API通信エラー */
    public const EXTERNAL_API_COMMUNICATION_ERROR = 1008;

    /** @var int App Store Server API Rate Limitエラー */
    public const APPSTORE_API_RATE_LIMIT_ERROR = 1009;

    // GooglePlayのエラーコード
    /** @var int 購入キャンセルステータスのレシートだった */
    public const GOOGLEPLAY_RECEIPT_STATUS_CANCELED = 2001;

    /** @var int 購入ペンディングステータスのレシートだった */
    public const GOOGLEPLAY_RECEIPT_STATUS_PENDING = 2002;

    /** @var int その他、正常ではないステータスだった */
    public const GOOGLEPLAY_RECEIPT_STATUS_OTHER = 2003;

    /** @var int GooglePlay公開鍵の読み込みに失敗した */
    public const GOOGLEPLAY_PUBLIC_KEY_LOAD_FAILED = 2004;
}
