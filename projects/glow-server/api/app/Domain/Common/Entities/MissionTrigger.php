<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

use App\Domain\Mission\Utils\MissionUtil;

class MissionTrigger
{
    protected string $criterionType = '';
    private ?string $criterionValue = null;
    private int $progress = 0;

    public function __construct(
        string $criterionType,
        ?string $criterionValue,
        int $progress,
    ) {
        $this->criterionType = $criterionType;
        $this->criterionValue = $criterionValue;
        $this->progress = $progress;
    }

    public function getCriterionType(): string
    {
        return $this->criterionType;
    }

    public function getCriterionValue(): ?string
    {
        return $this->criterionValue;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function isValid(): bool
    {
        return MissionUtil::isValidCriterionType($this->criterionType);
    }

    public function getCriterionKey(): string
    {
        return MissionUtil::makeCriterionKey($this->criterionType, $this->criterionValue);
    }
}
