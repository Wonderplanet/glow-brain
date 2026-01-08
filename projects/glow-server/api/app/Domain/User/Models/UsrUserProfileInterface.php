<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Usr\Entities\UsrUserProfileEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrUserProfileInterface extends UsrModelInterface
{
    public function getMyId(): string;

    public function getName(): string;

    public function setName(string $newName, CarbonImmutable $nameUpdateAt): void;

    public function getNameUpdateAt(): ?string;

    public function getBirthDate(): ?int;

    public function setBirthDate(int $birth_date): void;

    public function hasBirthDate(): bool;

    public function getMstUnitId(): string;

    public function setMstUnitId(string $mstUnitId): void;

    public function getMstEmblemId(): string;

    public function setMstEmblemId(string $mstEmblemId): void;

    public function setNameUpdateAt(?CarbonImmutable $nameUpdateAt): void;

    public function isFirstNameChange(): bool;

    public function toEntity(): UsrUserProfileEntity;
}
