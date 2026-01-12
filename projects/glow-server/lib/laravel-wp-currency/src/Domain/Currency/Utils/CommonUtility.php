<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Utils;

use Illuminate\Support\Facades\Config;
use WonderPlanet\Domain\Currency\Enums\FreeCurrencyType;

/**
 * 共通のユーティリティメソッドをまとめたクラス
 *
 * 広く共通で使用されて、かつ分類が難しいメソッドをまとめる。
 */
class CommonUtility
{
    /**
     * デバッグ機能実行可能環境かを判定する
     *
     * @return boolean
     */
    public static function isDebuggableEnvironment(): bool
    {
        // 設定から判定用ファンクションを読み込み
        $isDebuggableEnvironmentFunction = Config::get('wp_currency.is_debuggable_environment_function');

        // ファンクションが未設定であればfalse
        if (is_null($isDebuggableEnvironmentFunction) || $isDebuggableEnvironmentFunction === '') {
            return false;
        }

        // ファンクションがなければfalse
        if (is_string($isDebuggableEnvironmentFunction) && !function_exists($isDebuggableEnvironmentFunction)) {
            return false;
        }

        // ファンクションの結果を返す
        return (bool)$isDebuggableEnvironmentFunction();
    }

    /**
     * サンドボックスデータ参照可能か判定
     *  デバッグ機能実行可能環境またはenable_sandbox_aggregation=trueなら表示
     *  どちらもfalseなら非表示
     * @return bool
     */
    public static function enableSandboxAggregation(): bool
    {
        return self::isDebuggableEnvironment()
            || config('wp_currency.enable_sandbox_aggregation');
    }

    /**
     * 無償一次通貨種類による付与数の振り分けを行う
     *
     * @param string $type
     * @param integer $amount
     * @return array<int> [$ingameAmount, $bonusAmount, $rewardAmount]
     */
    public static function getFreeAmountByType(
        string $type,
        int $amount,
    ): array {
        // typeによる付与先の分岐
        // FreeCurrencyAddEntityとCurrencyServiceでロジックを共通化したかったので、ここに処理を移動
        $ingameAmount = 0;
        $bonusAmount = 0;
        $rewardAmount = 0;

        // 特定のtypeに制限するため、enumを使用
        switch (FreeCurrencyType::from($type)) {
            case FreeCurrencyType::Ingame:
                $ingameAmount = $amount;
                break;
            case FreeCurrencyType::Bonus:
                $bonusAmount = $amount;
                break;
            case FreeCurrencyType::Reward:
                $rewardAmount = $amount;
                break;
        }

        return [$ingameAmount, $bonusAmount, $rewardAmount];
    }

    /**
     * TTM(公表仲値)を計算する
     * ttm(公表仲値) = (TTS＋TTB）/ 2
     *
     * @param string $tts
     * @param string $ttb
     * @return string
     */
    public static function calcTtm(string $tts, string $ttb): string
    {
        // ttm(公表仲値) = (TTS＋TTB）/ 2
        // DBの桁数に合わせて小数点以下6位までを返却する
        return bcdiv(bcadd($tts, $ttb, 6), '2', 6);
    }

    /**
     * 通貨単位でTTS, TTBを割って1通貨あたりのTTS, TTBになるように計算する
     * @param string $val // TTS or TTB
     * @param string $perUnit // 1unit, 100unitなどの通貨単位情報
     * @return string
     */
    public static function calcTtsOrTtbWithPerUnit(string $val, string $perUnit): string
    {
        // unitの文字列を除外して数値だけにする
        $baseUnit = str_replace('unit', '', $perUnit);
        return bcdiv($val, $baseUnit, 6);
    }

    /**
     * TWD, MYRのTTS, TTBを計算する
     * $val = TTS or TTB / 円 となっており、それを TTS or TTB / TWD のように1通貨あたりの値に補正するため
     * $baseにperYenの値を設定する
     * @param string $base $valの単位になる数値
     * @param string $val $base円あたりの数値
     * @return string
     */
    public static function calcAndRoundRateForTWDAndYMR(string $base, string $val): string
    {
        // TWDはper yen, MYRはper 100yenとなっているため、1通貨あたり何円という単位に合わせるため$base / $valを実行する
        // 小数点第七位まで保持
        $ratePerForeignRate = bcdiv($base, $val, 7);
        // 0.0000005を足して小数点第七位で四捨五入し、小数点第六位までを返す
        return bcadd($ratePerForeignRate, "0.0000005", 6);
    }
}
