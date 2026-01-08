<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Entities;

class PvpMatchingScoreRangeFormat
{
    public function __construct(
        private string $id,
        private string $classType,
        private int $classLevel,
        private int $startScoreRange,
        private int $endScoreRange,
        private int $winAddPoint,
        private int $loseSubPoint
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'id' => $this->id,
            'classType' => $this->classType,
            'classLevel' => $this->classLevel,
            'startScoreRange' => $this->startScoreRange,
            'endScoreRange' => $this->endScoreRange,
            'winAddPoint' => $this->winAddPoint,
            'loseSubPoint' => $this->loseSubPoint,
        ];
    }
}
