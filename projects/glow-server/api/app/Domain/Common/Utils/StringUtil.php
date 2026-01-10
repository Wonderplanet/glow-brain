<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

use Illuminate\Support\Str;

class StringUtil
{
    public static function snakeToPascalCase(string $str): string
    {
        return Str::studly($str);
    }

    /**
     * データ的に未指定と扱われる文字列データかどうかを判定する
     * true: 未指定, false: 指定されている
     */
    public static function isNotSpecified(?string $s): bool
    {
        return $s === null || $s === '' || $s === 'NULL' || $s === 'null';
    }

    /**
     * データ的に指定ありとして扱う文字列データかどうかを判定する
     * true: 指定あり, false: 未指定
     *
     * @param string|null $s
     * @return boolean
     */
    public static function isSpecified(?string $s): bool
    {
        return self::isNotSpecified($s) === false;
    }

    /**
     * 未指定かどうかを判定して、同じ結果であることを確認する
     * true: s1,s2で同じ結果
     * false: s1,s2で異なる結果
     *
     * @param string $s1
     * @param string $s2
     * @return boolean
     */
    public static function checkIfBothNotSpecified(string $s1, string $s2): bool
    {
        return self::isNotSpecified($s1) && self::isNotSpecified($s2);
    }

    public static function convertToISO8601(?string $dateString): ?string
    {
        if (is_null($dateString) || $dateString === '') {
            return $dateString;
        }

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
        return $dateTime->format(\DateTime::ATOM);
    }
}
