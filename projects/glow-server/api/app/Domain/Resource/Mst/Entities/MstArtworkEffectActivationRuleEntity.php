<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkEffectActivationRuleEntity
{
    public function __construct(
        private string $id,
        private string $mstArtworkEffectId,
        private string $conditionType,
        private string $conditionValue,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkEffectId(): string
    {
        return $this->mstArtworkEffectId;
    }

    public function getConditionType(): string
    {
        return $this->conditionType;
    }

    public function getConditionValue(): string
    {
        return $this->conditionValue;
    }
}
