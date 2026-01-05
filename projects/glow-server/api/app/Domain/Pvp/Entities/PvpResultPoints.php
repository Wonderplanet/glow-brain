<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Entities;

class PvpResultPoints
{
    public function __construct(
        private int $resultPoint,
        private int $clearTimeBonusPoint,
        private int $opponentBonusPoint,
    ) {
    }

    public function getResultPoint(): int
    {
        return $this->resultPoint;
    }

    public function getClearTimeBonusPoint(): int
    {
        return $this->clearTimeBonusPoint;
    }

    public function getOpponentBonusPoint(): int
    {
        return $this->opponentBonusPoint;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'resultPoint' => $this->resultPoint,
            'timeBonusPoint' => $this->clearTimeBonusPoint,
            'opponentBonusPoint' => $this->opponentBonusPoint,
        ];
    }
}
