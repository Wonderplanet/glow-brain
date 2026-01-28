<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils;

use Carbon\Carbon;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\SpreadSheetLabel;

class SpreadSheetSerialDate
{
    // スプレッドシートのシリアル値を日付に変換する
    public static function convertSerialDateToDateTime(string $serialDate, string $tz = SpreadSheetLabel::DATETIME_INPUT_TIMEZONE): Carbon
    {
        $base = Carbon::createStrict(1899, 12, 30, 0, 0, 0, $tz);
        $dates = explode('.', $serialDate);
        if (is_numeric($dates[0])) {
            $base = $base->addDays((int)$dates[0]);
        }
        if (isset($dates[1]) && is_numeric($dates[1])) {
            $seconds = (int)(((float)("0." . $dates[1])) * 24 * 60 * 60);
            $base = $base->addSeconds($seconds);
        }
        return $base;
    }
}
