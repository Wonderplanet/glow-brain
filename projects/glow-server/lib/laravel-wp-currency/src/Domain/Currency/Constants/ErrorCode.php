<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Constants;

/**
 * 通貨基盤で使用するエラーコードを定義するクラス
 *
 * ※課金基盤側のエラーコードはbillingを参照すること
 */
class ErrorCode
{
    /** @var int 有償一次通貨が不足している */
    public const NOT_ENOUGH_PAID_CURRENCY = 1;

    /** @var int 一次通貨が不足している */
    public const NOT_ENOUGH_CURRENCY = 2;

    /**
     * @var int 二次通貨が不足している
     *
     * 二次通貨は廃止されたため、このエラーコードは使用しない
     * 欠番とわかるようにエラーコードはコメントアウトして残しておく
     */
    // public const NOT_ENOUGH_CASH = 3;

    /** @var int 無償一次通貨情報が存在しない */
    public const NOT_FOUND_FREE_CURRENCY = 4;

    /** @var int 通貨管理情報(usr_currency_summary)が存在しない */
    public const NOT_FOUND_CURRENCY_SUMMARY = 5;

    /** @var int 有償一次通貨情報が存在しない */
    public const NOT_FOUND_PAID_CURRENCY = 6;

    /** @var int 有償一次通貨登録時の数量が0以下 */
    public const FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO = 11;

    /** @var int 一次通貨の所持数が上限を超えていて付与できない */
    public const ADD_CURRENCY_BY_OVER_MAX = 12;

    /** @var int 有償一次通貨レコードが存在しない */
    public const USR_CURRENCY_PAID_NOT_FOUND = 14;

    /** @var int 無償一次通貨の所持数が上限を超えていて付与できない */
    public const ADD_FREE_CURRENCY_BY_OVER_MAX = 15;

    /** @var int 有償一次通貨の所持数が上限を超えていて付与できない */
    public const ADD_PAID_CURRENCY_BY_OVER_MAX = 16;

    // 一次通貨返却
    /** @var int 一次通貨返却時の返却数が購入時の数を超えている */
    public const FAILED_TO_REVERT_CURRENCY_BY_OVER_PURCHASE_AMOUNT = 21;

    /** @var int 返却対象の有償一次通貨のseq_noが一致しない */
    public const FAILED_TO_REVERT_CURRENCY_BY_NOT_MATCH_SEQ_NO = 22;

    /** @var int 返却個数が不正な値 - 有償通貨 */
    public const FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_PAID = 23;

    /** @var int 返却個数が不正な値 - 無償通貨 */
    public const FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_FREE = 24;

    /** @var int 返却個数が不正な値 - 有償、無償の合計値 */
    public const FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_SUM = 25;

    /** @var int 返却個数が不正な値 - 返却実行中に返却個数が消費個数を上回っている */
    public const FAILED_TO_REVERT_INVALID_REVERT_COUNT_IN_REVERTING = 26;

    // その他
    /** @var int Debug機能が使用できない環境 */
    public const INVALID_DEBUG_ENVIRONMENT = 90;

    /** @var int 想定していない課金プラットフォームが指定されたエラー */
    public const UNKNOWN_BILLING_PLATFORM = 99;
}
