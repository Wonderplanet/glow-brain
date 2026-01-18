<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrUserParameterEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

class UsrUserParameter extends UsrEloquentModel implements UsrUserParameterInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
        'usr_user_id',
        'stamina',
    ];

    public function init(string $usrUserId, int $initialStamina, CarbonImmutable $now): void
    {
        $this->usr_user_id = $usrUserId;
        $this->level = 1;
        $this->exp = 0;
        $this->coin = 0;
        $this->stamina = $initialStamina;
        $this->stamina_updated_at = $now->toDateTimeString();
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    public function getCoin(): int
    {
        return $this->coin;
    }

    public function getStamina(): int
    {
        return $this->stamina;
    }

    public function getStaminaUpdatedAt(): ?string
    {
        return is_null($this->stamina_updated_at) ? null : (string) $this->stamina_updated_at;
    }

    public function addStamina(int $stamina, int $maxStamina): void
    {
        $this->stamina = min($this->stamina + $stamina, $maxStamina);
    }

    public function subtractStamina(int $stamina): void
    {
        $this->stamina = max($this->stamina - $stamina, 0);
    }

    public function subtractCoin(int $coin): void
    {
        $this->coin -= $coin;
    }

    public function addCoin(int $coin): void
    {
        $this->coin += $coin;
    }

    public function setCoin(int $coin): void
    {
        $this->coin = $coin;
    }

    public function setStamina(int $stamina): void
    {
        $this->stamina = $stamina;
    }

    public function setStaminaUpdatedAt(?string $staminaUpdatedAt): void
    {
        $this->stamina_updated_at = $staminaUpdatedAt;
    }

    public function addExp(int $exp): void
    {
        $this->exp += $exp;
    }

    public function setExp(int $exp): void
    {
        $this->exp = $exp;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }

    public function toEntity(): UsrUserParameterEntity
    {
        return new UsrUserParameterEntity(
            $this->usr_user_id,
            $this->level,
            $this->exp,
            $this->coin,
            $this->stamina,
            $this->stamina_updated_at,
        );
    }
}
