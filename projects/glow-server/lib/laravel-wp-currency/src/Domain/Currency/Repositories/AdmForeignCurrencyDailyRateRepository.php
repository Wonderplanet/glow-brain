<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Illuminate\Support\Collection;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyDailyRate;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class AdmForeignCurrencyDailyRateRepository
{
    /**
     * 日毎外貨為替相場情報登録
     *
     * @param int $year
     * @param int $month
     * @param int $day
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
        int $day,
        string $currency,
        string $currencyName,
        string $currencyCode,
        string $tts,
        string $ttb
    ): string {
        // ttm(公表仲値) = (TTS＋TTB）/ 2
        $ttm = CommonUtility::calcTtm($tts, $ttb);

        $model = new AdmForeignCurrencyDailyRate();
        $model->year = $year;
        $model->month = $month;
        $model->day = $day;
        $model->currency = $currency;
        $model->currency_name = $currencyName;
        $model->currency_code = $currencyCode;
        $model->tts = $tts;
        $model->ttb = $ttb;
        $model->ttm = $ttm;
        $model->save();

        return $model->id;
    }

    /**
     * 年と月と日から対象のデータを配列で取得
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return Collection<int, AdmForeignCurrencyDailyRate>
     */
    public function getCollectionByYearAndMonthAndDay(int $year, int $month, int $day): Collection
    {
        return AdmForeignCurrencyDailyRate::query()
            ->where('year', $year)
            ->where('month', $month)
            ->where('day', $day)
            ->get();
    }

    /**
     * 年と月から対象のデータを配列で取得
     *
     * @param int $year
     * @param int $month
     * @return Collection<int, AdmForeignCurrencyDailyRate>
     */
    public function getCollectionByYearAndMonth(int $year, int $month): Collection
    {
        return AdmForeignCurrencyDailyRate::query()
            ->where('year', $year)
            ->where('month', $month)
            ->get();
    }

    /**
     * 最新の外貨為替相場情報を取得
     *
     * @return AdmForeignCurrencyDailyRate|null
     */
    public function getLatest(): ?AdmForeignCurrencyDailyRate
    {
        return AdmForeignCurrencyDailyRate::query()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('day', 'desc')
            ->first();
    }
}
