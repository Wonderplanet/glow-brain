<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

class MathUtil
{
    /**
     * 値の範囲を最小と最大で指定して、
     * 最大を超えていれば最大、最小を下回っていれば最小、それ以外はそのままの値を返す
     */
    public function clipInt(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    /**
     * 小数点以下の桁数を指定して切り捨て
     * 例)floorToPrecision(123.456, 2) => 123.45
     *
     * @param float $number
     * @param int   $precision
     * @return float
     */
    public static function floorToPrecision(float $number, int $precision): float
    {
        $factor = pow(10, $precision);
        return floor($number * $factor) / $factor;
    }
}
