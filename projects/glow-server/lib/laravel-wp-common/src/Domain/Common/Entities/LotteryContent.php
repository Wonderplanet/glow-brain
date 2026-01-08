<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Entities;

class LotteryContent
{
    public function __construct(
        private int|float $weight,
        private mixed $content,
    ) {
    }

    public function getWeight(): int|float
    {
        return $this->weight;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function isValid(): bool
    {
        return $this->weight > 0;
    }
}
