<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use App\Domain\Resource\Usr\Models\UsrModel;
use Carbon\CarbonImmutable;

class UsrStage extends UsrModel implements UsrStageInterface
{
    protected static string $tableName = 'usr_stages';
    protected array $modelKeyColumns = ['usr_user_id', 'mst_stage_id'];
    private bool $isFirstClear = false;

    public static function create(
        string $usrUserId,
        string $mstStageId,
        ?CarbonImmutable $now = null,
    ): UsrStageInterface {
        return new self([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'clear_count' => 0,
            'clear_time_ms' => null,
        ]);
    }

    public function getMstStageId(): string
    {
        return $this->attributes['mst_stage_id'];
    }

    public function isClear(): bool
    {
        return $this->getClearCount() > 0;
    }

    public function getClearCount(): int
    {
        return $this->attributes['clear_count'];
    }

    public function incrementClearCount(): void
    {
        if ($this->attributes['clear_count'] === 0) {
            $this->isFirstClear = true;
        }
        $this->attributes['clear_count']++;
    }

    public function addClearCount(int $addNum): void
    {
        if ($this->attributes['clear_count'] === 0) {
            $this->isFirstClear = true;
        }
        $this->attributes['clear_count'] += $addNum;
    }

    public function isFirstClear(): bool
    {
        return $this->isFirstClear;
    }

    public function getClearTimeMs(): ?int
    {
        return $this->attributes['clear_time_ms'];
    }

    public function setClearTimeMs(int $clearTimeMs): void
    {
        if ($this->getClearTimeMs() === null) {
            $this->attributes['clear_time_ms'] = $clearTimeMs;
            return;
        }

        $this->attributes['clear_time_ms'] = min($this->getClearTimeMs(), $clearTimeMs);
    }
}
