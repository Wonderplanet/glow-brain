<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories\Contracts;

use App\Domain\Mission\Enums\MissionCriterionConditionType;
use App\Domain\Mission\Enums\MissionCriterionType;

interface IMissionCriterion
{
    public function getType(): MissionCriterionType;

    public function getValue(): null|string;

    /**
     * @return array<MissionCriterionConditionType>
     */
    public function getConditionTypes(): array;

    public function isClearCondition(): bool;

    public function isUnlockCondition(): bool;

    public function getCriterionKey(): string;
}
