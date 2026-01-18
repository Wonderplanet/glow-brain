<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;

class MstTutorialEntity
{
    public function __construct(
        private string $id,
        private string $type,
        private int $sortOrder,
        private string $functionName,
        private string $conditionType,
        private string $conditionValue,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    public function isMainPartCompleted(): bool
    {
        // メインパート完了
        return $this->type === TutorialType::MAIN->value &&
            $this->functionName === TutorialFunctionName::MAIN_PART_COMPLETED->value;
    }

    public function getConditionType(): string
    {
        return $this->conditionType;
    }

    public function getConditionValue(): string
    {
        return $this->conditionValue;
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
