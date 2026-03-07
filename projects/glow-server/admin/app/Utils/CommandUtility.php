<?php

namespace App\Utils;

use Carbon\Carbon;

/**
 * コマンドで使用する想定のメソッド群を定義
 */
class CommandUtility
{
    private const TIMEZONE_JST = 'Asia/Tokyo';

    /**
     * @param ?string $input
     * @return string エラー内容の文言を返す。エラーでなければ空文字を返す
     */
    public static function validateYearMonth(?string $input): string
    {
        if (is_null($input)) {
            // nullの場合は処理をスキップ
            return '';
        }

        // 形式チェック
        if (!preg_match('/^\d{4}-\d{2}$/', $input)) {
            return 'yearMonthオプションはYYYY-MM形式である必要があります。';
        }

        // 分割して検証
        [$year, $month] = explode('-', $input);
        $month = (int)$month;

        if ($month < 1 || $month > 12) {
            return '月は1〜12の値である必要があります。';
        }

        return '';
    }

    /**
     * 現在日時をJSTで取得
     *
     * @return Carbon
     */
    public static function getNow(): Carbon
    {
        return Carbon::now(self::TIMEZONE_JST);
    }

    /**
     * デフォルトの年と月を取得
     * コマンド実行日時から-1ヶ月した年月を指定
     *
     * @return array<int, int>
     */
    public static function defaultYearAndMonth(): array
    {
        $now = self::getNow();
        $now = $now->subMonth();
        return [$now->year, $now->month];
    }
}
