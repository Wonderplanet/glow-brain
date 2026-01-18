<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

class PartyStatus
{
    public function __construct(
        private readonly string $usrUnitId,
        private readonly string $mstUnitId,
        private readonly string $color,
        private readonly string $roleType,
        private readonly int $hp,
        private readonly int $atk,
        private readonly string $moveSpeed,
        private readonly int $summonCost,
        private readonly int $summonCoolTime,
        private readonly int $damageKnockBackCount,
        private readonly ?string $specialAttackMstAttackId,
        private readonly int $attackDelay,
        private readonly int $nextAttackInterval,
        private readonly ?string $mstUnitAbility1,
        private readonly ?string $mstUnitAbility2,
        private readonly ?string $mstUnitAbility3,
    ) {
    }


    public function getUsrUnitId(): string
    {
        return $this->usrUnitId;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getRoleType(): string
    {
        return $this->roleType;
    }

    public function getHp(): int
    {
        return $this->hp;
    }

    public function getAtk(): int
    {
        return $this->atk;
    }

    public function getMoveSpeed(): string
    {
        return $this->moveSpeed;
    }

    public function getSummonCost(): int
    {
        return $this->summonCost;
    }

    public function getSummonCoolTime(): int
    {
        return $this->summonCoolTime;
    }

    public function getDamageKnockBackCount(): int
    {
        return $this->damageKnockBackCount;
    }

    public function getSpecialAttackMstAttackId(): ?string
    {
        return $this->specialAttackMstAttackId;
    }

    public function getAttackDelay(): int
    {
        return $this->attackDelay;
    }

    public function getNextAttackInterval(): int
    {
        return $this->nextAttackInterval;
    }

    public function getMstUnitAbility1(): ?string
    {
        return $this->mstUnitAbility1;
    }

    public function getMstUnitAbility2(): ?string
    {
        return $this->mstUnitAbility2;
    }

    public function getMstUnitAbility3(): ?string
    {
        return $this->mstUnitAbility3;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'usr_unit_id' => $this->usrUnitId,
            'mst_unit_id' => $this->mstUnitId,
            'color' => $this->color,
            'role_type' => $this->roleType,
            'hp' => $this->hp,
            'atk' => $this->atk,
            'move_speed' => $this->moveSpeed,
            'summon_cost' => $this->summonCost,
            'summon_cool_time' => $this->summonCoolTime,
            'damage_knock_back_count' => $this->damageKnockBackCount,
            'special_attack_mst_attack_id' => $this->specialAttackMstAttackId,
            'attack_delay' => $this->attackDelay,
            'next_attack_interval' => $this->nextAttackInterval,
            'mst_unit_ability1' => $this->mstUnitAbility1,
            'mst_unit_ability2' => $this->mstUnitAbility2,
            'mst_unit_ability3' => $this->mstUnitAbility3,
        ];
    }
}
