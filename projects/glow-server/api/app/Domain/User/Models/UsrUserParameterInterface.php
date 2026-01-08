<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Usr\Entities\UsrUserParameterEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrUserParameterInterface extends UsrModelInterface
{
    public function getLevel(): int;

    public function getExp(): int;

    public function getCoin(): int;

    public function getStamina(): int;

    public function getStaminaUpdatedAt(): ?string;

    public function setStaminaUpdatedAt(?string $staminaUpdatedAt): void;

    public function subtractStamina(int $stamina): void;

    public function addCoin(int $coin): void;

    public function setCoin(int $coin): void;

    public function subtractCoin(int $coin): void;

    public function addStamina(int $stamina, int $maxStamina): void;

    public function setStamina(int $stamina): void;

    public function addExp(int $exp): void;

    public function setExp(int $exp): void;

    public function setLevel(int $level): void;

    public function toEntity(): UsrUserParameterEntity;
}
