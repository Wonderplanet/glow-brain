<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use Illuminate\Support\Collection;

class ForeignCurrencyMonthlyRateEntity
{
    /**
     * @var Collection<string, AdmForeignCurrencyRateEntity>
     */
    private Collection $admForeignCurrencyRateEntities;

    /**
     * @var Collection<int, Collection<string, AdmForeignCurrencyDailyRateEntity>>
     */
    private Collection $admForeignCurrencyDailyRateEntities;

    /**
     * @param int $year 年
     * @param int $month 月
     * @param Collection<int, AdmForeignCurrencyRateEntity> $admForeignCurrencyRateEntities 月末外貨為替相場情報
     * @param Collection<int, AdmForeignCurrencyDailyRateEntity> $admForeignCurrencyDailyRateEntities 日毎外貨為替相場情報
     */
    public function __construct(
        private int $year,
        private int $month,
        Collection $admForeignCurrencyRateEntities,
        Collection $admForeignCurrencyDailyRateEntities = new Collection(),
    ) {
        $this->admForeignCurrencyRateEntities = $admForeignCurrencyRateEntities->keyBy(
            fn(AdmForeignCurrencyRateEntity $entity) => $entity->getCurrencyCode()
        );
        $this->admForeignCurrencyDailyRateEntities = $admForeignCurrencyDailyRateEntities
            ->groupBy(fn(AdmForeignCurrencyDailyRateEntity $entity) => $entity->getDay())
            ->map(fn(Collection $entities) => $entities->keyBy(
                fn(AdmForeignCurrencyDailyRateEntity $entity) => $entity->getCurrencyCode()
            ));
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getRate(int $day, string $currencyCode): float
    {
        if (
            $this->admForeignCurrencyDailyRateEntities->has($day) &&
            $this->admForeignCurrencyDailyRateEntities[$day]->has($currencyCode)
        ) {
            return (float)$this->admForeignCurrencyDailyRateEntities[$day][$currencyCode]->getTtm();
        }
        if ($this->admForeignCurrencyRateEntities->has($currencyCode)) {
            return (float)$this->admForeignCurrencyRateEntities[$currencyCode]->getTtm();
        }

        // 対象日と月にデータが無い場合、直近の日付からrateを取る
        for ($day = $day - 1; $day >= 1; $day--) {
            if (
                $this->admForeignCurrencyDailyRateEntities->has($day) &&
                $this->admForeignCurrencyDailyRateEntities[$day]->has($currencyCode)
            ) {
                return (float)$this->admForeignCurrencyDailyRateEntities[$day][$currencyCode]->getTtm();
            }
        }

        // 上記で見つからなかった場合、1.0倍で返す
        return 1.0;
    }
}
