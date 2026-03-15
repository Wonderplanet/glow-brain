<?php

declare(strict_types=1);

namespace App\Domain\Common\Constants;

class ErrorCode
{
    // 共通(0番台)

    /** @var int HttpStatusCode::ERROR以外のHTTPやインフラレイヤーでのエラーコードパディング用。特にこのコードをハンドリングでは利用しない */
    public const HTTP_ERROR = 0;

    /** @var int 不明なエラー */
    public const UNKNOWN_ERROR = 1;

    /**
     * リクエストパラメータに不備がある
     *
     * $request->validate()のチェックに問題がある可能性が高い
     * 送信しているリクエストJSONを確認すること
     *
     * @var int
     */
    public const VALIDATION_ERROR = 2;

    /** @var int アクセストークンの検証に失敗 */
    public const INVALID_ACCESS_TOKEN = 3;

    /** @var int IDトークンの検証に失敗 */
    public const INVALID_ID_TOKEN = 4;

    /** @var int データ連携の認証に失敗 */
    public const DEVICE_LINK_AUTH_FAILED = 5;

    /** @var int ユーザー作成に失敗（UUIDの重複？） */
    public const USER_CREATE_FAILED = 6;

    /** @var int ユーザーが見つからない（アカウント削除済み？） */
    public const USER_NOT_FOUND = 7;

    /** @var int ユニットが見つからない */
    public const UNIT_NOT_FOUND = 8;

    /** @var int 消費リソース不足 */
    public const LACK_OF_RESOURCES = 9;

    /** @var int マスターデータがが見つからない */
    public const MST_NOT_FOUND = 10;

    /** @var int ユニットのレベルが十分でない */
    public const UNIT_INSUFFICIENT_LEVEL = 11;

    /** @var int 重複登録 */
    public const DUPLICATE_ENTRY = 12;

    /** @var int 想定外の値を使用 */
    public const INVALID_PARAMETER = 13;

    /** @var int 抽選結果がない */
    public const NO_LOTTERY_RESULT = 14;

    /** @var int 名前のクール時間中 */
    public const CHANGE_NAME_COOL_TIME = 15;

    /** @var int NGワードが使用されている */
    public const PLAYER_NAME_USED_NG_WORD = 16;

    /** @var int バイト数チェック（半角で18文字、全角で9文字が最大） */
    public const PLAYER_NAME_OVER_BYTE = 17;

    /** @var int 頭文字にスペースが使われている（左が半角スペース、右が全角スペース） */
    public const PLAYER_NAME_SPACE_FIRST = 18;

    /** @var int 対象のアバターを所持していない */
    public const AVATAR_NOT_FOUND = 19;

    /** @var int 不明なプラットフォームだった */
    public const INVALID_PLATFORM = 20;

    /** @var int ユーザーの生年月日が既に登録されている */
    public const USER_BIRTHDATE_ALREADY_REGISTERED = 21;

    /** @var int ユーザーの生年月日が未登録 */
    public const USER_BIRTHDATE_NOT_REGISTERED = 22;

    /** @var int デバイスが見つからない */
    public const DEVICE_NOT_FOUND = 23;

    /** @var int 他のデバイスでログインしたためアクセストークンが無効化された */
    public const MULTIPLE_DEVICE_LOGIN_DETECTED = 24;

    /**
     * ショップ関連(200番台)
     */
    // 広告商品購入時に指定されたIDが広告商品のものではない
    public const SHOP_COST_TYPE_NOT_AD = 201;
    // 購入上限に達している商品を購入しようとした
    public const SHOP_TRADE_COUNT_LIMIT = 202;
    // 未開放の条件パックを購入しようとした
    public const SHOP_CONDITION_PACK_NOT_RELEASED = 203;
    // 期限切れの条件パックを購入しようとした
    public const SHOP_CONDITION_PACK_EXPIRED = 204;
    // 有効期限内のパスを購入しようとした
    public const SHOP_PASS_NOT_EXPIRED = 205;
    // 課金購入ではサポートしていない商品を購入しようとした
    public const SHOP_PURCHASE_PRODUCT_TYPE_NOT_SUPPORTED = 206;

    /**
     * 放置収益関連(300番台)
     */

    /** @var int 報酬が受け取れない */
    public const IDLE_INCENTIVE_CANNOT_RECEIVE_REWARD = 301;

    /** @var int クイック探索の実行制限回数を超えている */
    public const IDLE_INCENTIVE_QUICK_RECEIVE_COUNT_LIMIT = 302;

    // ステージ(1000番台)

    /** @var int ステージに挑戦していない */
    public const STAGE_NOT_START = 1001;

    /** @var int ステージのマスタデータが存在しない */
    public const STAGE_NOT_FOUND = 1002;

    /** @var int ステージに挑戦できない */
    public const STAGE_CANNOT_START = 1003;

    /** @var int ステージ宝箱を受け取れない */
    public const STAGE_CANNOT_RECEIVE_TREASURE = 1004;

    /** @var int ステージのコンティニュー上限を超えている */
    public const STAGE_CONTINUE_LIMIT = 1005;

    public const QUEST_PERIOD_OUTSIDE = 1006;

    public const EVENT_PERIOD_OUTSIDE = 1007;

    public const STAGE_EVENT_PERIOD_OUTSIDE = 1008;

    public const QUEST_TYPE_NOT_FOUND = 1009;

    public const STAGE_EVENT_PERIOD_DUPLICATE = 1010;

    /** @var int パーティがルールに適してない */
    public const STAGE_EVENT_PARTY_VIOLATION_RULE = 1011;

    /** @var int コンティニューできないステージ */
    public const STAGE_CAN_NOT_CONTINUE = 1012;

    /** @var int スタミナブーストできないステージ */
    public const STAGE_CAN_NOT_AUTO_LAP = 1014;

    /** @var int 残りステージ挑戦回数より多くスタミナブーストしようとした */
    public const STAGE_CAN_NOT_AUTO_LAP_CHALLENGE_LIMIT = 1015;

    // アイテム(2000番台)

    /** @var int アイテム未所持 */
    public const ITEM_NOT_OWNED = 2001;
    public const ITEM_AMOUNT_IS_NOT_ENOUGH = 2002;

    /** @var int アイテムの交換上限に達していて、交換不可 */
    public const ITEM_TRADE_AMOUNT_LIMIT_EXCEEDED = 2003;

    // ガチャ(3000番台)

    /** @var int 広告制限でガシャが引けない(回数制限やインターバル中) */
    public const GACHA_CANNOT_AD_LIMIT_DRAW = 3001;

    /** @var int 広告制限でガシャが引けない(回数制限やインターバル中) */
    public const GACHA_CANNOT_AD_INTERVAL_DRAW = 3002;

    /** @var int ガシャ排出物に想定外のリソースが含まれている */
    public const GACHA_NOT_EXPECTED_RESOURCE_TYPE = 3003;

    /** @var int 抽選BOXの中身が空 */
    public const GACHA_BOX_IS_EMPTY = 3004;

    /** @var int 許可されてないガシャ実行の仕方 */
    public const GACHA_TYPE_UNEXPECTED = 3005;

    /** @var int 不正なコストによる引き方 */
    public const GACHA_UNJUST_COSTS = 3006;

    /** @var int ガシャ実行回数が上限 */
    public const GACHA_PLAY_LIMIT = 3007;

    /** @var int ガシャの既に引いている数がクライアントと違う */
    public const GACHA_DREW_COUNT_DIFFERENT = 3008;

    /** @var int 想定外のコスト消費 */
    public const GACHA_NOT_EXPECTED_COST = 3009;

    /** @var int リクエストされたN連数が不正値 */
    public const GACHA_NOT_EXPECTED_PLAY_NUM = 3010;

    /** @var int N時間開放ガシャの有効期限が切れている */
    public const GACHA_EXPIRED = 3011;

    /** @var int ステップアップガシャのマスターデータが存在しない */
    public const GACHA_STEPUP_NOT_FOUND = 3012;

    /** @var int ステップアップガシャのステップ数がクライアントとサーバーで不一致 */
    public const GACHA_STEPUP_STEP_MISMATCH = 3013;

    /** @var int ステップアップガシャの周回数上限に達している */
    public const GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED = 3014;

    // ユーザー（4000番台）

    /** @var int スタミナ購入の回数制限 */
    public const USER_BUY_STAMINA_COUNT_LIMIT = 4001;

    /** @var int 広告視聴スタミナ購入の広告視聴インターバル中 */
    public const USER_BUY_STAMINA_AD_DURING_INTERVAL = 4002;

    /** @var int スタミナが最大のためスタミナを購入できない */
    public const USER_STAMINA_FULL = 4003;

    /** @var int スタミナ購入によりシステム上限を超える */
    public const USER_STAMINA_EXCEEDS_LIMIT = 4004;

    /** @var int エンブレムを所持していない */
    public const EMBLEM_NOT_OWNED = 4005;

    /** @var int BNIDアクセストークン取得APIエラー */
    public const USER_BNID_ACCESS_TOKEN_API_ERROR = 4006;

    /** @var int BNIDと連携していない */
    public const USER_BNID_NOT_LINKED = 4007;

    /** @var int 連携先のアカウントが連携制限中 */
    public const USER_ACCOUNT_LINKING_RESTRICTED_OTHER_ACCOUNT = 4008;

    /** @var int BNIDが他のアカウントと連携済み */
    public const USER_BNID_LINKED_OTHER_USER = 4009;

    /** @var int 自身アカウントが連携制限中 */
    public const USER_ACCOUNT_LINKING_RESTRICTED_MY_ACCOUNT = 4010;

    // ユニット（5000番台）

    /** @var int ユニットを既に所持している */
    public const UNIT_ALREADY_OWNED = 5001;

    /** @var int レベルリセットができない */
    public const UNIT_CANNOT_RESET_LEVEL = 5002;

    /** @var int レベルの値が不正 */
    public const UNIT_LEVE_UP_INVALID_LEVEL = 5003;

    /** @var int レベルが上限を超えている */
    public const UNIT_LEVEL_UP_EXCEED_LIMIT_LEVEL = 5004;

    // ショップ（6000番台）

    /** @var int 無料購入ができないコイン商品 */
    public const SHOP_COIN_PRODUCT_IS_NOT_FREE = 6001;

    /** @var int コイン購入の回数制限 */
    public const SHOP_BUY_COIN_COUNT_LIMIT = 6002;

    // パーティ（7000番台）

    /** @var int  ユニット数が異常 */
    public const PARTY_INVALID_UNIT_COUNT = 7001;

    /** @var int  パーティNOが異常 */
    public const PARTY_INVALID_PARTY_NO = 7002;

    /** @var int ユニットIDの重複 */
    public const PARTY_DUPLICATE_UNIT_ID = 7003;

    /** @var int ユニットIDが不正 */
    public const PARTY_INVALID_UNIT_ID = 7004;

    /** @var int パーティ名が不正 */
    public const PARTY_INVALID_PARTY_NAME = 7005;

    /** @var int 原画パーティの原画重複 */
    public const PARTY_DUPLICATE_ARTWORK_ID = 7101;

    /** @var int 原画パーティの原画IDが不正 */
    public const PARTY_INVALID_ARTWORK_ID = 7102;

    /** @var int 原画パーティの原画数不正 */
    public const PARTY_INVALID_ARTWORK_COUNT = 7103;

    // ミッション（8000番台）

    /** @var int ミッションの報酬の受取ができない */
    public const MISSION_CANNOT_RECEIVE_REWARD = 8001;

    /** @var int ミッションのクリアができない */
    public const MISSION_CANNOT_CLEAR = 8002;

    /** @var int イベント期間外でミッション報酬の受取ができない */
    public const MISSION_CANNOT_RECEIVE_OUT_PERIOD_EVENT = 8003;

    /** @var int 期間外で期間限定ミッション報酬の受取ができない */
    public const MISSION_CANNOT_RECEIVE_OUT_PERIOD_LIMITED_TERM = 8004;

    // ゲート (9000番台)

    /** @var int 原画を所持していない */
    public const ARTWORK_NOT_OWNED = 9001;

    /** @var int ゲートを所持していない */
    public const OUTPOST_NOT_OWNED = 9002;

    // 図鑑 (9500番台)
    public const ENCYCLOPEDIA_NOT_REACHED_ENCYCLOPEDIA_RANK = 9501;
    public const ENCYCLOPEDIA_REWARD_RECEIVED = 9502;
    public const ENCYCLOPEDIA_NOT_IS_NEW = 9503;
    public const ENCYCLOPEDIA_DATA_NOT_FOUND = 9504;

    // その他(9x000番台)

    // 96000番台(SEED取り込み時に番号がバッティングした)
    /** @var int 配信中のマスターリリース情報が存在しない */
    public const NOT_FOUND_APPLY_MASTER_RELEASE = 96004;

    /** @var int クライアントバージョンと互換性のあるマスターデータが存在しない */
    public const INCOMPATIBLE_MASTER_DATA_FROM_CLIENT_VERSION = 96005;

    /** @var int 配信中のアセットリリース情報が存在しない */
    public const NOT_FOUND_APPLY_ASSET_RELEASE = 96006;

    /** @var int アドミンデバッグで失敗した時 */
    public const ADMIN_DEBUG_FAILED = 97001;

    /** @var int クライアントのバージョン更新が必要 */
    public const REQUIRE_CLIENT_VERSION_UPDATE = 98001;

    /** @var int バージョン更新が必要 */
    public const REQUIRE_RESOURCE_UPDATE = 98002;

    /** @var int 有効なアセットバージョンが存在しない */
    public const AVAILABLE_ASSET_VERSION_NOT_FOUND = 98003;

    /** @var int 日付が更新されました */
    public const CROSS_DAY = 98004;

    /** @var int 不正行為による、時限BANで、アカウント停止中のユーザー */
    public const USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING = 98005;

    /** @var int 異常なデータを検出したことによる、時限BANで、アカウント停止中のユーザー */
    public const USER_ACCOUNT_BAN_TEMPORARY_BY_DETECTED_ANOMALY = 98006;

    /** @var int 永久BANで、アカウント停止中のユーザー */
    public const USER_ACCOUNT_BAN_PERMANENT = 98007;

    /** @var int アカウント削除対応で、アカウント削除済みのユーザー */
    public const USER_ACCOUNT_DELETED = 98008;

    /** @var int 返金対応中でアカウント一時停止中のユーザー */
    public const USER_ACCOUNT_REFUNDING = 98009;

    /** @var int 同一APIのロックデータが既に存在する */
    public const API_MULTIPLE_ACCESS_ERROR = 98010;

    /** @var int コンテンツメンテナンス中 */
    public const CONTENT_MAINTENANCE = 99003;

     /** @var int Cleanupが必要なコンテンツメンテナンス中 */
    public const CONTENT_MAINTENANCE_NEED_CLEANUP = 99004;

    /** @var int マスターDBの接続先とreleaseControlの接続先情報が異なる */
    public const MASTER_DATABASE_CONNECTIONS_DIFFERENT = 99005;

    /** @var int コンテンツメンテナンス外 */
    public const CONTENT_MAINTENANCE_OUTSIDE = 99006;


    // 課金・通貨基盤関連のエラー (10000番台)

    /** @var int allowanceで購入不可 */
    public const BILLING_ALLOWANCE_FAILED = 10001;

    /** @var int レシート検証/購入不可の商品を買ってしまった */
    public const BILLING_VERIFY_RECEIPT_FAILED = 10002;

    /** @var int レシート検証/不正レシート */
    public const BILLING_VERIFY_RECEIPT_INVALID_RECEIPT = 10003;

    /** @var int 処理済みの重複レシート */
    public const BILLING_VERIFY_RECEIPT_DUPLICATE_RECEIPT = 10004;

    // 課金管理(Billing)のエラー (11000番台)

    /** @var int ショップ情報レコードが存在していない */
    public const BILLING_SHOP_INFO_NOT_FOUND = 11001;

    /** @var int 使用できない環境 */
    public const BILLING_INVALID_ENVIRONMENT = 11002;

    /** @var int 未対応の課金プラットフォーム */
    public const BILLING_UNSUPPORTED_BILLING_PLATFORM = 11003;

    /** @var int 許可レコード(allowance)が不正 */
    public const BILLING_INVALID_ALLOWANCE = 11004;

    // マスタデータの検証
    /** @var int allowanceとOprProductが不整合 */
    public const BILLING_ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH = 11005;

    /** @var int allowanceとMstStoreProductが不整合 */
    public const BILLING_ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH = 11006;

    // AppStoreのエラーコード
    /** @var int AppStoreからの応答ステータスがOKではない(AppStoreのみ) */
    public const BILLING_APPSTORE_RESPONSE_STATUS_NOT_OK = 11007;

    /** @var int AppStoreのバンドルIDが一致しない(AppStoreのみ) */
    public const BILLING_APPSTORE_BUNDLE_ID_NOT_MATCH = 11008;

    /** @var int 設定からbunndle_idが取得できない(AppStoreのみ) */
    public const BILLING_APPSTORE_BUNDLE_ID_NOT_SET = 11009;

    // GooglePlayのエラーコード
    /** @var int 購入キャンセルステータスのレシートだった(GooglePlayのみ) */
    public const BILLING_GOOGLEPLAY_RECEIPT_STATUS_CANCELED = 11010;

    /** @var int 購入ペンディングステータスのレシートだった(GooglePlayのみ) */
    public const BILLING_GOOGLEPLAY_RECEIPT_STATUS_PENDING = 11011;

    /** @var int その他、正常ではないステータスだった(GooglePlayのみ) */
    public const BILLING_GOOGLEPLAY_RECEIPT_STATUS_OTHER = 11012;

    /** @var int 未成年の購入限度額超過エラー */
    public const BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED = 11013;

    /** @var int 購入トランザクション終了用購入上限 */
    public const BILLING_TRANSACTION_END_PURCHASE_LIMIT = 11014;

    /** @var int 購入トランザクション終了 */
    public const BILLING_TRANSACTION_END = 11015;

    /** @var int その他、想定していないエラー */
    public const BILLING_UNKNOWN_ERROR = 11999;

    // 通貨管理(Currency)のエラー (12000番台

    /** @var int 有償一次通貨が不足している */
    public const CURRENCY_NOT_ENOUGH_PAID_CURRENCY = 12001;

    /** @var int 一次通貨が不足している */
    public const CURRENCY_NOT_ENOUGH_CURRENCY = 12002;

    /** @var int 二次通貨が不足している(廃止予定) */
    public const CURRENCY_NOT_ENOUGH_CASH = 12003;

    /** @var int 無償一次通貨情報が存在しない */
    public const CURRENCY_NOT_FOUND_FREE_CURRENCY = 12004;

    /** @var int 通貨管理情報(usr_currency_summary)が存在しない */
    public const CURRENCY_NOT_FOUND_CURRENCY_SUMMARY = 12005;

    /** @var int 有償一次通貨登録時の数量が0以下 */
    public const CURRENCY_FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO = 12006;

    /** @var int 一次通貨の所持数が上限を超えていて付与できない */
    public const CURRENCY_ADD_CURRENCY_BY_OVER_MAX = 12007;

    // その他
    /** @var int デバッグ機能が使用できない環境 */
    public const CURRENCY_INVALID_DEBUG_ENVIRONMENT = 12008;

    /** @var int 想定していない課金プラットフォームが指定されたエラー */
    public const CURRENCY_UNKNOWN_BILLING_PLATFORM = 12009;

    /** @var int 有償一次通貨の上限を超える付与はできない */
    public const CURRENCY_ADD_PAID_CURRENCY_BY_OVER_MAX = 12010;

    /** @var int 無償一次通貨の上限を超える付与はできない */
    public const CURRENCY_ADD_FREE_CURRENCY_BY_OVER_MAX = 12011;

    /** @var int その他、想定していないエラー */
    public const CURRENCY_UNKNOWN_ERROR = 12999;

    // メッセージボックス (13000番台)

    /** @var int メッセージ既読日時更新に失敗 */
    public const FAILURE_UPDATE_BY_MESSAGE_OPENED_AT = 13001;

    /** @var int 対象ユーザーが持っていないメッセージIDだった
     */
    public const USR_MESSAGE_NOT_FOUND = 13002;

    /** @var int 期限切れの報酬メッセージだった */
    public const EXPIRED_MESSAGE_RESOURCE = 13003;

    /** @var int メッセージ報酬受け取り処理でエラー */
    public const ERROR_RECEIVED_MESSAGE_RESOURCE = 13004;

    /** @var int ユーザーメッセージ日時(既読 or 受け取り)更新に失敗 */
    public const FAILURE_UPDATE_BY_USER_MESSAGES = 13005;

    /** @var int メッセージ報酬受け取り上限超過エラー */
    public const MESSAGE_REWARD_BY_OVER_MAX = 13006;

    // 降臨バトル (14000番台)

    /** @var int 降臨バトルが期間外 */
    public const ADVENT_BATTLE_PERIOD_OUTSIDE = 14001;

    /** @var int 降臨バトルに挑戦できない */
    public const ADVENT_BATTLE_CANNOT_START = 14002;

    /** @var int 降臨バトルのセッションデータ不整合 */
    public const ADVENT_BATTLE_SESSION_MISMATCH = 14003;

    /** @var int 不明な降臨バトルタイプ */
    public const ADVENT_BATTLE_TYPE_NOT_FOUND = 14004;

    /** @var int 不明な降臨バトル報酬カテゴリー */
    public const ADVENT_BATTLE_REWARD_CATEGORY_NOT_FOUND = 14005;

    /** @var int 降臨バトルランキングが期間外 */
    public const ADVENT_BATTLE_RANKING_OUT_PERIOD = 14006;

    // チュートリアル (15000番台)

    /** @var int チュートリアルの進行が想定しない順序になっている */
    public const TUTORIAL_INVALID_MAIN_PART_ORDER = 15001;

    // PvP (16000番台)
    /** @var int 開催期間外 */
    public const PVP_SEASON_PERIOD_OUTSIDE = 16001;

    /** @var int 挑戦権が無い */
    public const PVP_NO_CHALLENGE_RIGHT = 16002;

    /** @var int 有効なセッションが見つからない */
    public const PVP_SESSION_NOT_FOUND = 16003;

    /** @var int 選出していないユーザー */
    public const PVP_NOT_SELECTED_USER = 16005;


    // 部分メンテナンスクリーンアップAPI(17xxx)
    /** @var int cleanupできない */
    public const CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED = 17001;

    // 交換所 (18000番台)
    /** @var int 交換所が開催期間外 */
    public const EXCHANGE_NOT_TRADE_PERIOD = 18001;

    /** @var int 交換所の交換上限を超過 */
    public const EXCHANGE_LINEUP_TRADE_LIMIT_EXCEEDED = 18002;

    /** @var int 交換所とラインナップの不整合 */
    public const EXCHANGE_LINEUP_MISMATCH = 18003;

    // BOXガチャ (19000番台)

    /** @var int BOXガチャが期間外 */
    public const BOX_GACHA_PERIOD_OUTSIDE = 19001;

    /** @var int BOXガチャの残数が足りない */
    public const BOX_GACHA_NOT_ENOUGH_STOCK = 19002;

    /** @var int BOXガチャの纏めて引ける数を超えている */
    public const BOX_GACHA_EXCEED_DRAW_LIMIT = 19003;

    /** @var int BOXガチャの箱レベルが不一致 */
    public const BOX_GACHA_BOX_LEVEL_MISMATCH = 19004;


    // 外部決済(WebStore) (20000番台)
    /** @var int ユーザーが存在しない */
    public const WEBSTORE_USER_NOT_FOUND = 20001;

    /** @var int 国コードが未登録 */
    public const WEBSTORE_COUNTRY_NOT_REGISTERED = 20003;

    /** @var int 内部エラー */
    public const WEBSTORE_INTERNAL_ERROR = 20004;

    /** @var int トランザクションが見つからない */
    public const WEBSTORE_TRANSACTION_NOT_FOUND = 20005;

    /** @var int 商品が見つからない */
    public const WEBSTORE_PRODUCT_NOT_FOUND = 20006;

    /** @var int 年齢制限エラー */
    public const WEBSTORE_AGE_RESTRICTION = 20008;

    /** @var int リソース（コイン・アイテム・有償通貨）の所持上限を超えるため、購入不可 */
    public const WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED = 20009;

    /** @var int 本番環境でテスト決済は利用不可 */
    public const WEBSTORE_SANDBOX_NOT_ALLOWED_IN_PRODUCTION = 20010;
}
