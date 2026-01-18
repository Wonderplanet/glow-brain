<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Entities;

class PvpEncyclopediaEffect
{
    public function __construct(
        private string $mstEncyclopediaEffectId
    ) {
    }

    public function getMstEncyclopediaEffectId(): string
    {
        return $this->mstEncyclopediaEffectId;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'mstEncyclopediaEffectId' => $this->mstEncyclopediaEffectId,
        ];
    }
}
