<?php
namespace App\Utils;
use Illuminate\Database\Eloquent\Builder;


class TimeUtil
{
    public static function formatJapanese(?string $dateTimeString) : string
    {
        if (empty($dateTimeString)) {
            return '';
        }
        $dt = new \DateTime($dateTimeString, new \DateTimeZone('Asia/Tokyo'));
        return $dt->format('Y年m月d日 H時i分s秒');
    }

    /**
     * 指定された日時データと同日のデータを取得する条件をクエリに追加する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $columnName
     * @param array<mixed> $data
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function addWhereBetweenByDay(Builder $query, string $columnName, array $data): Builder
    {
        if (blank($data[$columnName])) {
            return $query;
        }
        return $query->whereBetween(
            $columnName,
            [
                date( "Y-m-d" , strtotime($data[$columnName])).' 00:00',
                date( "Y-m-d" , strtotime($data[$columnName])).' 23:59'
            ]
        );
    }

     /**
     * 指定された検索日時と指定された検索日時の範囲のデータ取得する条件をクエリに追加する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $columnName
     * @param string $start
     * @param string $end
     * @param array<mixed> $data
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function addWhereBetweenByDayRange(Builder $query, string $columnName, string $start, string $end, array $data): Builder
    {
        if (blank($data[$start]) && blank($data[$end])) {
            return $query;
        }
        return $query->whereBetween(
            $columnName,
            [
                date( "Y-m-d" , strtotime($data[$start])).' 00:00',
                date( "Y-m-d" , strtotime($data[$end])).' 23:59'
            ]
        );
    }
    
}
