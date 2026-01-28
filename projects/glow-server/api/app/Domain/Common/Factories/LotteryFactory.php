<?php

declare(strict_types=1);

namespace App\Domain\Common\Factories;

use App\Domain\Common\Entities\Lottery;
use App\Domain\Common\Entities\LotteryContent;
use App\Domain\Common\Utils\RandomUtil;
use App\Domain\Gacha\Entities\NoPrizeContent;
use Illuminate\Support\Collection;

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
     * @param Collection $weightMap key: id, value: weight
     * @param Collection $contentMap key: id, value: content
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

    /**
     * ハズレ枠を含む抽選機を生成する
     * @param Collection $weightMap key: id, value: weight
     * @param Collection $contentMap key: id, value: content
     * @param int $noPrizeWeight ハズレ枠の重み
     */
    public function createFromMapWithNoPrize(Collection $weightMap, Collection $contentMap, int $noPrizeWeight): Lottery
    {
        $noPrizeKey = 'noPrize';
        $weightMap->put($noPrizeKey, $noPrizeWeight);
        $contentMap->put($noPrizeKey, new NoPrizeContent());

        return $this->createFromMaps($weightMap, $contentMap);
    }
}
