<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Illuminate\Support\Collection;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class AdmForeignCurrencyRateRepository
{
    /**
     * 月末外貨為替相場情報登録
     *
     * @param int $year
     * @param int $month
     * @param string $currency
     * @param string $currencyName
     * @param string $currencyCode
     * @param string $tts
     * @param string $ttb
     * @return string
     */
    public function insert(
        int $year,
        int $month,
        string $currency,
        string $currencyName,
        string $currencyCode,
        string $tts,
        string $ttb
    ): string {
        // ttm(公表仲値) = (TTS＋TTB）/ 2
        $ttm = CommonUtility::calcTtm($tts, $ttb);

        $admForeignCurrencyRate = new AdmForeignCurrencyRate();
        $admForeignCurrencyRate->year = $year;
        $admForeignCurrencyRate->month = $month;
        $admForeignCurrencyRate->currency = $currency;
        $admForeignCurrencyRate->currency_name = $currencyName;
        $admForeignCurrencyRate->currency_code = $currencyCode;
        $admForeignCurrencyRate->tts = $tts;
        $admForeignCurrencyRate->ttb = $ttb;
        $admForeignCurrencyRate->ttm = $ttm;

        $admForeignCurrencyRate->save();

        return $admForeignCurrencyRate->id;
    }

    /**
     * 年と月から対象のデータを配列で取得
     *
     * @param int $year
     * @param int $month
     * @return Collection<int, AdmForeignCurrencyRate>
     */
    public function getCollectionByYearAndMonth(int $year, int $month): Collection
    {
        return AdmForeignCurrencyRate::query()
            ->where('year', $year)
            ->where('month', $month)
            ->get();
    }

    /**
     * 年と月から対象のデータを削除
     *
     * @param int $year
     * @param int $month
     */
    public function deleteByYearAndMonth(int $year, int $month): void
    {
        AdmForeignCurrencyRate::query()
            ->where('year', $year)
            ->where('month', $month)
            ->forceDelete();
    }

    /**
     * currency_codeに合わせた月末TTMをクエリ結果で取得する為のcase文を生成する
     *
     * @param int $year
     * @param int $month
     * @return string
     */
    public function makeCurrencyRateCaseQueryStr(int $year, int $month): string
    {
        $collection = $this->getCollectionByYearAndMonth($year, $month);

        $caseQuery = 'CASE';
        $caseQuery .= " WHEN currency_code = 'JPY' THEN '1'"; // 国内通貨は1固定

        if ($collection->count() === 0) {
            // 対象年月の外貨為替レートが存在しない場合は国内通貨以外空欄となるようにする
            $caseQuery .= " ELSE ''";
            $caseQuery .= " END AS currency_rate";
            return $caseQuery;
        }

        $collection->map(function (AdmForeignCurrencyRate $row) use (&$caseQuery) {
            $caseQuery .= " WHEN currency_code = '{$row->currency_code}' THEN '{$row->ttm}'";
        });
        $caseQuery .= " ELSE ''";
        $caseQuery .= " END AS currency_rate";

        return $caseQuery;
    }
}
