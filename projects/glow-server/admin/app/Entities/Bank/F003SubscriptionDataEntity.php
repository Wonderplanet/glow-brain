<?php

declare(strict_types=1);

namespace App\Entities\Bank;

class F003SubscriptionDataEntity
{
    /** @var string アイテムの名称 */
    private string $name = '';

    /** @var float 販売価格 */
    private float $price = 0.0;

    /** @var int 期間内アイテム数 */
    private int $totalCount = 0;

    /** @var int 期間単位 */
    private int $unit= 0;

    /** @var int 期間 */
    private int $span = 0;

    public function __construct(
        string $name,
        float $price,
        int $unit,
        int $span
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->unit = $unit;
        $this->span = $span;
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

    public function getUnit(): int
    {
        return $this->unit;
    }

    public function getSpan(): int
    {
        return $this->span;
    }

    public function formatToLog(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'total_count' => $this->totalCount,
            'unit' => $this->unit,
            'span' => $this->span,
        ];
    }
}
