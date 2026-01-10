<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstAttackEntity
{
    public function __construct(
        private string $id,
        private string $mst_unit_id,
        private int $unit_grade,
        private string $attack_kind,
        private string $killer_colors,
        private int $killer_percentage,
        private int $action_frames,
        private int $attack_delay,
        private int $next_attack_interval,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstUnitId(): string
    {
        return $this->mst_unit_id;
    }

    public function getUnitGrade(): int
    {
        return $this->unit_grade;
    }

    public function getAttackKind(): string
    {
        return $this->attack_kind;
    }

    public function getKillerColors(): string
    {
        return $this->killer_colors;
    }

    public function getKillerPercentage(): int
    {
        return $this->killer_percentage;
    }

    public function getActionFrames(): int
    {
        return $this->action_frames;
    }

    public function getAttackDelay(): int
    {
        return $this->attack_delay;
    }

    public function getNextAttackInterval(): int
    {
        return $this->next_attack_interval;
    }
}
