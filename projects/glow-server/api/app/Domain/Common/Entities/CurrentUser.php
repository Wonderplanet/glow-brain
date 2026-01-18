<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

use App\Domain\Common\Enums\UserStatus;
use Carbon\CarbonImmutable;

/** @immutable */
class CurrentUser
{
    public function __construct(
        public string $id,
        public string $gameStartAt,
        public int $status = UserStatus::NORMAL->value,
        public ?string $suspendEndAt = null,
    ) {
    }

    /**
     * ユーザーIDを取得する
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getUsrUserId(): string
    {
        return $this->id;
    }

    public function getGameStartAt(): string
    {
        return $this->gameStartAt;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getSuspendEndAt(): ?string
    {
        return $this->suspendEndAt;
    }

    /**
     * ユーザーが利用停止中かどうかを判定する
     *
     * @param CarbonImmutable $now UTCの現在日時
     */
    public function isSuspended(CarbonImmutable $now): bool
    {
        switch ($this->status) {
            case UserStatus::BAN_TEMPORARY_CHEATING->value:
            case UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value:
                if ($this->suspendEndAt === null) {
                    // 時限BANの終了日時が未設定の場合は、利用停止中とみなす
                    return true;
                }
                $suspendEndAt = CarbonImmutable::parse($this->suspendEndAt); // UTC
                // now <= suspendEndAt の時 true になる
                return $now->lte($suspendEndAt);
            case UserStatus::DELETED->value:
            case UserStatus::BAN_PERMANENT->value:
            case UserStatus::REFUNDING->value:
                return true;
            default:
                return false;
        }
    }
}
