<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities\Contracts;

interface IMstStageRewardEntity
{
    public function getId(): string;

    public function getMstStageId(): string;

    public function getRewardCategory(): string;

    public function getResourceType(): string;

    public function getResourceId(): ?string;

    public function getResourceAmount(): int;

    public function getPercentage(): int;

    public function getReleaseKey(): int;

    public function isFirstClear(): bool;

    public function isAlways(): bool;

    public function isRandom(): bool;
}
