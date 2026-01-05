<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\InGame\Enums\InGameSpecialRuleType;

class MstInGameSpecialRuleEntity
{
    public function __construct(
        private string $id,
        private string $contentType,
        private string $targetId,
        private string $ruleType,
        private ?string $ruleValue,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function getRuleType(): string
    {
        return $this->ruleType;
    }

    public function getRuleTypeEnum(): InGameSpecialRuleType
    {
        return InGameSpecialRuleType::from($this->ruleType);
    }

    public function getRuleValue(): ?string
    {
        return $this->ruleValue;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }
}
