<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use Illuminate\Support\Collection;

readonly class GachaLotteryBox
{
    public function __construct(
        private Collection $regularLotteryBox,
        private ?Collection $fixedLotteryBox,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Gacha\Entities\GachaBoxInterface>
     */
    public function getRegularLotteryBox(): Collection
    {
        return $this->regularLotteryBox;
    }

    /**
     * @return Collection<\App\Domain\Gacha\Entities\GachaBoxInterface>|null
     */
    public function getFixedLotteryBox(): ?Collection
    {
        return $this->fixedLotteryBox;
    }
}
