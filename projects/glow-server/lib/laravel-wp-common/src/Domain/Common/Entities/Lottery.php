<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Entities;

use WonderPlanet\Domain\Common\Utils\RandomUtil;

class Lottery
{
    private int $totalWeight;

    /**
     * @param  array<LotteryContent> $lots
     */
    public function __construct(
        private array $lots,
        private RandomUtil $randomUtil,
    ) {
        // 重みの合計を計算
        $totalWeight = 0;
        foreach ($this->lots as $lot) {
            if ($lot->isValid()) {
                $totalWeight += $lot->getWeight();
            }
        }
        $this->totalWeight = (int) $totalWeight;
    }

    public function draw(?string $seed = null): mixed
    {
        return $this->drawInt($seed);
    }

    private function drawInt(?string $seed = null): mixed
    {
        $lotWeight = $this->randomUtil->randomInt(1, $this->totalWeight, $seed);
        $sumWeight = 0;
        foreach ($this->lots as $lot) {
            if (!$lot->isValid()) {
                continue;
            }
            $sumWeight += $lot->getWeight();
            if ($lotWeight <= $sumWeight) {
                return $lot->getContent();
            }
        }

        return null;
    }
}
