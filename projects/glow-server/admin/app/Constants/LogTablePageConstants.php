<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * ログテーブルページの定数
 */
class LogTablePageConstants
{
    /**
     * 作成日時範囲フィルター名
     */
    public const CREATED_AT_RANGE = 'created_at_range';

    /**
     * APIリクエストIDフィルター名
     */
    public const NGINX_REQUEST_ID = 'nginx_request_id';

    /**
     * 日付範囲フィルターの開始日時フィールド名
     */
    public const CREATED_AT_RANGE_START_AT = 'start_at';

    /**
     * 日付範囲フィルターの終了日時フィールド名
     */
    public const CREATED_AT_RANGE_END_AT = 'end_at';

    /**
     * デフォルトの共通フィルター名一覧
     */
    public const DEFAULT_COMMON_FILTERS = [
        self::CREATED_AT_RANGE,
        self::NGINX_REQUEST_ID,
    ];

    /**
     * ログテーブルのフィルターで指定できる日付範囲の最大日数
     *
     * データベースの負荷軽減や、Athenaクエリ実行時のコスト増加防止のために制限を設ける
     */
    public const DATE_RANGE_LIMIT_DAYS = 10;
}
