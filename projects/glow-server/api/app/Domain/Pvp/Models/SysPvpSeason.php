<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Models;

use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Domain\Resource\Sys\Models\SysModel;
use App\Domain\Resource\Traits\HasFactory;
use Carbon\CarbonImmutable;

class SysPvpSeason extends SysModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setStartAt(CarbonImmutable $startAt): void
    {
        $this->start_at = $startAt->toDateTimeString();
    }

    public function setEndAt(CarbonImmutable $endAt): void
    {
        $this->end_at = $endAt->toDateTimeString();
    }

    public function setClosedAt(CarbonImmutable $closedAt): void
    {
        $this->closed_at = $closedAt->toDateTimeString();
    }

    public function toEntity(): SysPvpSeasonEntity
    {
        return new SysPvpSeasonEntity(
            $this->id,
            CarbonImmutable::parse($this->start_at),
            CarbonImmutable::parse($this->end_at),
            $this->closed_at ? CarbonImmutable::parse($this->closed_at) : null
        );
    }
}
