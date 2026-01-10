<?php

declare(strict_types=1);

namespace App\Entities\Bank;

class F003DataEntity
{
    /** @var string コインの名称 */
    private string $name = '';

    /** @var int 販売コイン数 */
    private int $coin = 0;

    /** @var float 販売価格 */
    private float $price = 0.0;

    /** @var int 期間内発行数 */
    private int $totalCount = 0;

    /** @var int 期間内消費数 */
    private int $totalConsumption = 0;

    public function __construct(
        string $name,
        int $coin,
        float $price,
    ) {
        $this->name = $name;
        $this->coin = $coin;
        $this->price = $price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCoin(): int
    {
        return $this->coin;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function addTotalCount(int $totalCount): void
    {
        $this->totalCount += $totalCount;
    }

    public function getTotalConsumption(): int
    {
        return $this->totalConsumption;
    }

    public function addTotalConsumption(int $totalConsumption): void
    {
        $this->totalConsumption += $totalConsumption;
    }

    public function formatToLog(): array
    {
        return [
            'name' => $this->name,
            'coin' => $this->coin,
            'price' => $this->price,
            'total_count' => $this->totalCount,
            'total_consumption' => $this->totalConsumption,
        ];
    }
}
