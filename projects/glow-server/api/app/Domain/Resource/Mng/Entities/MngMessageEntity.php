<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

use Carbon\CarbonImmutable;

class MngMessageEntity
{
    public function __construct(
        private string $id,
        private string $startAt,
        private string $expiredAt,
        private string $type,
        private ?string $accountCreatedStartAt,
        private ?string $accountCreatedEndAt,
        private int $addExpiredDays,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    /**
     * メッセージの表示期限
     * @return string
     */
    public function getExpiredAt(): string
    {
        return $this->expiredAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAccountCreatedStartAt(): ?string
    {
        return $this->accountCreatedStartAt;
    }

    public function getAccountCreatedEndAt(): ?string
    {
        return $this->accountCreatedEndAt;
    }

    /**
     * メッセージ受取ってから報酬受取可能な期間の日数
     * @return int
     */
    public function getAddExpiredDays(): int
    {
        return $this->addExpiredDays;
    }

    /**
     * 延長後の最終期限
     * @return CarbonImmutable
     */
    public function getFinalExpiredAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->expiredAt)
            ->addDays($this->addExpiredDays);
    }

    public function isActive(CarbonImmutable $now): bool
    {
        return $now->between(
            $this->startAt,
            $this->expiredAt,
        );
    }
}
