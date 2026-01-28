<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Models;

use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

class UsrGachaUpper extends UsrEloquentModel implements UsrGachaUpperInterface
{
    use HasFactory;

    protected $fillable = [
    ];

    protected $casts = [
    ];

    public function init(string $usrUserId, string $upperGroup, string $upperType): void
    {
        $this->id = $this->newUniqueId();
        $this->usr_user_id = $usrUserId;
        $this->upper_group = $upperGroup;
        $this->upper_type = $upperType;
        $this->count = 0;
    }

    public function getUpperGroup(): string
    {
        return $this->upper_group;
    }

    public function getUpperType(): string
    {
        return $this->upper_type;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function addCount(int $count = 1): void
    {
        $this->count += $count;
    }

    public function resetCount(int $count = 0): void
    {
        $this->count = $count;
    }

    public function isMaxRarity(): bool
    {
        // 最高レアリティの天井データか
        return $this->upper_type === UpperType::MAX_RARITY->value;
    }

    public function isPickup(): bool
    {
        // ピックアップの天井データか
        return $this->upper_type === UpperType::PICKUP->value;
    }
}
