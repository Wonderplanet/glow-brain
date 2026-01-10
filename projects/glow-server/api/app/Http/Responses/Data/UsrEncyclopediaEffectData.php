<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class UsrEncyclopediaEffectData
{
    public function __construct(
        private string $effectType,
        private float $value,
    ) {
    }

    public function getEffectType(): string
    {
        return $this->effectType;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
