<?php

declare(strict_types=1);

namespace App\Entities;
use App\Constants\SystemConstants;
use Carbon\CarbonImmutable;

class MaintenanceEntity
{
    private string $pk;

    private string $sk;

    /**
     * 開始日時(UTC)のunixタイムスタンプ
     * @var int
     */
    private int $startAt;

    /**
     * 終了日時(UTC)のunixタイムスタンプ
     * @var int
     */
    private int $endAt;

    private bool $isValid;

    private string $text;

    public function __construct(array $data)
    {
        $this->pk = data_get($data, 'PK.S', '');
        $this->sk = data_get($data, 'SK.S', '');
        $this->startAt = (int) data_get($data, 'start_at.N', 0);
        $this->endAt = (int) data_get($data, 'end_at.N', 0);
        $this->isValid = (bool) data_get($data, 'is_valid.BOOL', false);
        $this->text = data_get($data, 'text.S', '');
    }

    public function getPk(): string
    {
        return $this->pk;
    }

    public function getSk(): string
    {
        return $this->sk;
    }

    public function getStartAt(): int
    {
        return $this->startAt;
    }

    public function getEndAt(): int
    {
        return $this->endAt;
    }

    public function getStartAtCarbon(): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp($this->startAt, SystemConstants::TIMEZONE_UTC);
    }

    public function getEndAtCarbon(): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp($this->endAt, SystemConstants::TIMEZONE_UTC);
    }

    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    public function isEnable(): bool
    {
        return $this->isValid;
    }

    public function isDisable(): bool
    {
        return !$this->isValid;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isUnderMaintenance(CarbonImmutable $now): bool
    {
        if ($this->isDisable()) {
            return false;
        }

        // UTCで比較処理を行うために変換
        $now = $now->setTimezone(SystemConstants::TIMEZONE_UTC);

        return $now->between(
            $this->getStartAtCarbon(),
            $this->getEndAtCarbon(),
            true,
        );
    }

    public function getStatusContent(CarbonImmutable $now): string
    {
        $now = $now->setTimezone(SystemConstants::TIMEZONE_UTC);

        if ($this->isDisable()) {
            return '無効';
        } else {
            if ($now->isBefore($this->getStartAtCarbon())) {
                return 'メンテ前';
            } elseif ($now->isAfter($this->getEndAtCarbon())) {
                return '終了済';
            } elseif ($this->isUnderMaintenance($now)) {
                return 'メンテ中';
            }
        }

        return '-';
    }

    public function getStatusBadgeColor(CarbonImmutable $now): string
    {
        $now = $now->setTimezone(SystemConstants::TIMEZONE_UTC);

        $content = $this->getStatusContent($now);

        return match ($content) {
            '無効' => 'gray',
            'メンテ前' => 'warning',
            'メンテ中' => 'danger',
            '終了済' => 'gray',
            default => 'gray',
        };
    }
}
