<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Usr\Entities\UsrUserLoginEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrUserLoginInterface extends UsrModelInterface
{
    public function getFirstLoginAt(): ?string;
    public function getLastLoginAt(): ?string;
    public function setHourlyAccessedAt(string $hourlyAccessedAt): void;
    public function getHourlyAccessedAt(): string;
    public function checkHourlyAccessUpdate(CarbonImmutable $now): bool;
    public function getLoginCount(): int;
    public function getLoginDayCount(): int;
    public function getLoginContinueDayCount(): int;
    public function getComebackDayCount(): int;

    public function incrementLoginDayCount(): void;
    public function incrementLoginContinueDayCount(): void;
    public function comebackLogin(int $comebackDayCount): void;
    public function login(CarbonImmutable $now): void;
    public function toEntity(): UsrUserLoginEntity;
}
