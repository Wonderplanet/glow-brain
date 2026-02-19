<?php

declare(strict_types=1);

namespace App\Entities\Bank;

use Illuminate\Support\Collection;

class F003Entity
{
    /** @var string プラットフォームID */
    private string $platformId = '';

    /** @var int 年月日 */
    private int $date = 0;

    /** @var Collection<F003DataEntity> 有償通貨情報 */
    private Collection $data;

    /** @var Collection<F003DirectDataEntity> 直接課金情報 */
    private Collection $directData;

    /** @var Collection<F003SubscriptionDataEntity> 定期購入情報 */
    private Collection $subscriptionData;

    public function __construct(
        string $platformId,
        int $date,
    ) {
        $this->platformId = $platformId;
        $this->date = $date;
        $this->data = new Collection();
        $this->directData = new Collection();
        $this->subscriptionData = new Collection();
    }

    public function getPlatformId(): string
    {
        return $this->platformId;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getTotalSales(): float
    {
        $total = $this->data->sum(function (F003DataEntity $dto) {
            $coin = $dto->getCoin();
            $price = $dto->getPrice();
            $consumption = $dto->getTotalConsumption();
            if ($coin === 0) {
                return 0.0;
            }

            // 寄り差分が出ない形で計算
            $units = intdiv($consumption, $coin);
            $remainder = $consumption % $coin;
            $unitSales = $units * $price;
            $remainderSales = $remainder * ($price / $coin);
            return $unitSales + $remainderSales;
        });
        return floor($total);
    }

    public function getDirectTotalSales(): float
    {
        return $this->directData->sum(function (F003DirectDataEntity $dto) {
            return $dto->getPrice() * $dto->getTotalCount();
        });
    }

    public function getSubscriptionTotalSales(): float
    {
        return $this->subscriptionData->sum(function (F003SubscriptionDataEntity $dto) {
            return $dto->getPrice() * $dto->getTotalCount();
        });
    }

    public function getData(string $id): ?F003DataEntity
    {
        return $this->data->get($id);
    }

    public function addData(string $id, F003DataEntity $data): void
    {
        $this->data->put($id, $data);
    }

    public function getDataJson(): string
    {
        return $this->data
            ->map(fn(F003DataEntity $dto) => $dto->formatToLog())
            ->values()
            ->toJson();
    }

    public function getDirectData(string $id): ?F003DirectDataEntity
    {
        return $this->directData->get($id);
    }

    public function addDirectData(string $id, F003DirectDataEntity $data): void
    {
        $this->directData->put($id, $data);
    }

    public function getDirectDataJson(): string
    {
        return $this->directData
            ->map(fn(F003DirectDataEntity $dto) => $dto->formatToLog())
            ->values()
            ->toJson();
    }

    public function getSubscriptionData(string $id): ?F003SubscriptionDataEntity
    {
        return $this->subscriptionData->get($id);
    }

    public function addSubscriptionData(string $id, F003SubscriptionDataEntity $data): void
    {
        $this->subscriptionData->put($id, $data);
    }

    public function getSubscriptionDataJson(): string
    {
        return $this->subscriptionData
            ->map(fn(F003SubscriptionDataEntity $dto) => $dto->formatToLog())
            ->values()
            ->toJson();
    }
}
