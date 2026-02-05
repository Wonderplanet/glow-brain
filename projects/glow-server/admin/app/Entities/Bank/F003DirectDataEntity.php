<?php

declare(strict_types=1);

namespace App\Entities\Bank;

class F003DirectDataEntity
{
    /** @var string アイテムの名称 */
    private string $name = '';

    /** @var float 販売価格 */
    private float $price = 0.0;

    /** @var int 期間内アイテム数 */
    private int $totalCount = 0;

    public function __construct(
        string $name,
        float $price,
    ) {
        $this->name = $name;
        $this->price = $price;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function formatToLog(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'total_count' => $this->totalCount,
        ];
    }
}
