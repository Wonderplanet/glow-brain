<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Gacha\Entities\GachaPrizeInterface;

class MstUnitEntity implements GachaPrizeInterface
{
    public function __construct(
        private string $id,
        private string $fragment_mst_item_id,
        private string $color,
        private string $role_type,
        private string $attack_range_type,
        private string $unit_label,
        private int $has_specific_rank_up,
        private string $mst_series_id,
        private string $asset_key,
        private string $rarity,
        private int $sort_order,
        private int $summon_cost,
        private int $summon_cool_time,
        private int $min_hp,
        private int $max_hp,
        private int $damage_knock_back_count,
        private string $move_speed,
        private float $well_distance,
        private int $min_attack_power,
        private int $max_attack_power,
        private string $mst_unit_ability_1,
        private int $ability_unlock_rank1,
        private string $mst_unit_ability_2,
        private int $ability_unlock_rank2,
        private string $mst_unit_ability_3,
        private int $ability_unlock_rank3,
        private int $is_encyclopedia_special_attack_position_right,
        private int $release_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFragmentMstItemId(): string
    {
        return $this->fragment_mst_item_id;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getAttackRangeType(): string
    {
        return $this->attack_range_type;
    }

    public function getRoleType(): string
    {
        return $this->role_type;
    }

    public function getUnitLabel(): string
    {
        return $this->unit_label;
    }

    public function getHasSpecificRankUp(): int
    {
        return $this->has_specific_rank_up;
    }

    public function getMstSeriesId(): string
    {
        return $this->mst_series_id;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getSortOrder(): int
    {
        return $this->sort_order;
    }

    public function getSummonCost(): int
    {
        return $this->summon_cost;
    }

    public function getSummonCoolTime(): int
    {
        return $this->summon_cool_time;
    }

    public function getMinHp(): int
    {
        return $this->min_hp;
    }

    public function getMaxHp(): int
    {
        return $this->max_hp;
    }

    public function getDamageKnockBackCount(): int
    {
        return $this->damage_knock_back_count;
    }

    public function getMoveSpeed(): string
    {
        return $this->move_speed;
    }

    public function getWellDistance(): float
    {
        return $this->well_distance;
    }

    public function getMinAttackPower(): int
    {
        return $this->min_attack_power;
    }

    public function getMaxAttackPower(): int
    {
        return $this->max_attack_power;
    }

    public function getMstUnitAbility1(): ?string
    {
        return $this->mst_unit_ability_1;
    }

    public function getAbilityUnlockRank1(): int
    {
        return $this->ability_unlock_rank1;
    }

    public function getMstUnitAbility2(): ?string
    {
        return $this->mst_unit_ability_2;
    }

    public function getAbilityUnlockRank2(): int
    {
        return $this->ability_unlock_rank2;
    }

    public function getMstUnitAbility3(): ?string
    {
        return $this->mst_unit_ability_3;
    }

    public function getAbilityUnlockRank3(): int
    {
        return $this->ability_unlock_rank3;
    }

    public function isEncyclopediaSpecialAttackPositionRight(): bool
    {
        return $this->is_encyclopedia_special_attack_position_right > 0;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }

    public function hasSpecificRankUp(): bool
    {
        return $this->has_specific_rank_up > 0;
    }
}
