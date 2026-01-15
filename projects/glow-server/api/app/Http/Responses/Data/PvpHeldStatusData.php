<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use Carbon\CarbonImmutable;

class PvpHeldStatusData
{
    public function __construct(
        private string $sysPvpSeasonId,
        private int $heldNumber,
        private CarbonImmutable $startAt,
        private CarbonImmutable $endAt,
    ) {
    }

    public function getSysPvpSeasonId(): string
    {
        return $this->sysPvpSeasonId;
    }

    public function getHeldNumber(): int
    {
        return $this->heldNumber;
    }

    public function getStartAt(): CarbonImmutable
    {
        return $this->startAt;
    }

    public function getEndAt(): CarbonImmutable
    {
        return $this->endAt;
    }

    /**
     * フォーマットされたレスポンスデータを返す
     *
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'sysPvpSeasonId' => $this->getSysPvpSeasonId(),
            'heldNumber' => $this->getHeldNumber(),
            'startAt' => $this->getStartAt()->toIso8601String(),
            'endAt' => $this->getEndAt()->toIso8601String(),
        ];
    }
}
