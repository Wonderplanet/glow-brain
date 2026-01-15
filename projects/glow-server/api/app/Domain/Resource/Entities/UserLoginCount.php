<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

class UserLoginCount
{
    /**
     * @param bool $isFirstLoginToday 本日初めてのログインならtrue
     */
    public function __construct(
        private ?string $beforeLoginAt,
        private string $currentLoginAt,
        private bool $isFirstLoginToday,
        private int $loginDayCount,
        private int $beforeLoginContinueDayCount,
        private int $loginContinueDayCount,
        private int $comebackDayCount,
    ) {
    }

    public function getBeforeLoginAt(): ?string
    {
        return $this->beforeLoginAt;
    }

    public function getCurrentLoginAt(): string
    {
        return $this->currentLoginAt;
    }

    public function getIsFirstLoginToday(): bool
    {
        return $this->isFirstLoginToday;
    }

    public function getLoginDayCount(): int
    {
        return $this->loginDayCount;
    }

    public function getBeforeLoginContinueDayCount(): int
    {
        return $this->beforeLoginContinueDayCount;
    }

    public function getLoginContinueDayCount(): int
    {
        return $this->loginContinueDayCount;
    }

    public function getComebackDayCount(): int
    {
        return $this->comebackDayCount;
    }

    public function isContinuousLogin(): bool
    {
        return $this->beforeLoginContinueDayCount < $this->loginContinueDayCount;
    }
}
