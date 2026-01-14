<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Factories;

use Illuminate\Support\Collection;
use WonderPlanet\Domain\Common\Entities\Lottery;
use WonderPlanet\Domain\Common\Entities\LotteryContent;
use WonderPlanet\Domain\Common\Utils\RandomUtil;

class LotteryFactory
{
    public function __construct(
        private RandomUtil $randomUtil,
    ) {
    }

    /**
     * @param  array<mixed, int> $lots
     */
    public function create(array $lots): Lottery
    {
        $lotteryContents = [];
        foreach ($lots as $key => $weight) {
            $lotteryContents[] = new LotteryContent($weight, $key);
        }
        return new Lottery($lotteryContents, $this->randomUtil);
    }

    /**
     * @param  array<LotteryContent> $lotteryContents
     */
    public function createFromLotteryContents(array $lotteryContents): Lottery
    {
        return new Lottery($lotteryContents, $this->randomUtil);
    }

    /**
     * @param Collection<int, mixed> $weightMap key: id, value: weight
     * @param Collection<int, mixed> $contentMap key: id, value: content
     */
    public function createFromMaps(
        Collection $weightMap,
        Collection $contentMap,
    ): Lottery {

        $lotteryContents = [];
        foreach ($weightMap as $key => $weight) {
            $content = $contentMap->get($key);
            if ($content === null) {
                continue;
            }
            $lotteryContents[] = new LotteryContent($weight, $content);
        }

        return new Lottery($lotteryContents, $this->randomUtil);
    }
}
