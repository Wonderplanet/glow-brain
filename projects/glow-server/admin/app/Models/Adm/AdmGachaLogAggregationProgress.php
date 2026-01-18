<?php

namespace App\Models\Adm;

use App\Constants\GachaLogAggregationProgressStatus;

class AdmGachaLogAggregationProgress extends AdmModel
{
    protected $casts = [
    ];

    protected $table = 'adm_gacha_log_aggregation_progresses';

    protected $guarded = [
    ];

    /**
     * @param array<mixed> $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->id)) {
            $this->id = $this->newUniqueId();
        }
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function addProgress(int $addProgress): void
    {
        $this->progress += $addProgress;
    }

    public function complete(): void
    {
        $this->status = GachaLogAggregationProgressStatus::COMPLETE->value;
    }

    public function isCompleted(): bool
    {
        return $this->status === GachaLogAggregationProgressStatus::COMPLETE->value;
    }

    public function getTargetDate(): string
    {
        return $this->target_date;
    }
}
