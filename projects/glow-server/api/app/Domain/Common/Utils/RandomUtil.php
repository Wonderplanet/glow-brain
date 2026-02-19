<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

class RandomUtil
{
    public function randomInt(int $min, int $max, ?string $seed = null): int
    {
        if (is_null($seed)) {
            return random_int($min, $max);
        }
        return $this->randomIntWithSeed($min, $max, $seed);
    }

    public function randomFloat(float $min, float $max): float
    {
        return $min + random_int(0, PHP_INT_MAX) / PHP_INT_MAX * ($max - $min);
    }

    private function randomIntWithSeed(int $min, int $max, ?string $seed = null): int
    {
        $engine = new Xoshiro256StarStar(hash('sha256', $seed, binary: true));
        $randomizer = new Randomizer($engine);
        return $randomizer->getInt($min, $max);
    }
}
