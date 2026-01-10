<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

use Carbon\CarbonImmutable;

class MngContentCloseEntity
{
    public function __construct(
        private string $id,
        private string $content_type,
        private ?string $content_id,
        private CarbonImmutable $start_at,
        private CarbonImmutable $end_at,
        private int $is_valid,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function getContentId(): ?string
    {
        return $this->content_id;
    }

    public function getStartAt(): CarbonImmutable
    {
        return $this->start_at;
    }

    public function getEndAt(): CarbonImmutable
    {
        return $this->end_at;
    }

    public function getIsValid(): int
    {
        return $this->is_valid;
    }

    /**
     * 現在時刻でアクティブかどうかを判定
     */
    public function isActiveAt(CarbonImmutable $now): bool
    {
        return $this->is_valid === 1
            && $now->between($this->start_at, $this->end_at);
    }
}
