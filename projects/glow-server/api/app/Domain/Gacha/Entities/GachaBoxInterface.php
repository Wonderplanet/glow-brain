<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Resource\Enums\RewardType;

interface GachaBoxInterface
{
    public function getId(): string;
    public function getGroupId(): string;
    public function getWeight(): int;
    public function getPickup(): bool;
    public function getResourceType(): RewardType;
    public function getResourceId(): ?string;
    public function getResourceAmount(): int;
    public function getRarity(): string;
    public function isUnit(): bool;
    public function isItem(): bool;
}
